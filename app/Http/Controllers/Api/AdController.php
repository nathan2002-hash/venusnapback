<?php

namespace App\Http\Controllers\Api;

use App\Models\Ad;
use Carbon\Carbon;
use App\Models\Adboard;
use App\Models\User;
use App\Models\Album;
use App\Models\AdClick;
use App\Models\AdMedia;
use App\Models\PointTransaction;
use App\Models\Supporter;
use App\Models\AdImpression;
use Illuminate\Http\Request;
use App\Jobs\AdImageCompress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdController extends Controller
{
    public function getUserAlbums(Request $request)
    {
        $user = $request->user();

        // Owned albums (creator/business only)
        $ownedAlbums = $user->albums()
            ->whereIn('type', ['creator', 'business'])
            ->select('id', 'name', 'type')
            ->get();

        // Shared albums (approved only, creator/business only)
        $sharedAlbumsRaw = DB::table('album_accesses')
            ->join('albums', 'album_accesses.album_id', '=', 'albums.id')
            ->where('album_accesses.user_id', $user->id)
            ->where('album_accesses.status', 'approved')
            ->whereIn('albums.type', ['creator', 'business'])
            ->select('albums.id', 'albums.name', 'albums.type')
            ->get();

        // Convert shared albums to Album-like objects
        $sharedAlbums = collect($sharedAlbumsRaw)->map(function ($album) {
            return [
                'id' => $album->id,
                'name' => $album->name,
                'type' => $album->type,
            ];
        });

        // Merge owned and shared, then map to clean output
        $albums = $ownedAlbums->map(function ($album) {
            return [
                'id' => $album->id,
                'name' => $album->name,
                'type' => $album->type,
            ];
        })->merge($sharedAlbums);

        // Final formatted output
        return response()->json([
            'albums' => $albums->map(function ($album) {
                $typeLabel = match($album['type']) {
                    'personal' => 'Personal',
                    'creator' => 'Creator',
                    default => 'Business',
                };

                return [
                    'id' => $album['id'],
                    'name' => "{$album['name']}",
                    'type' => "$typeLabel",
                ];
            })
        ]);
    }


    public function getUserPoints(Request $request)
    {
        // Get the authenticated user's albums, filtering for 'creator' and 'business' types only
        $available = Auth::user()->points;

        $available_points = (int) $available;
        // Return response in JSON format
        return response()->json([
            'available_points' => $available_points
        ]);
    }

    public function adboard(Request $request)
    {
        $user = Auth::user(); // Get authenticated user

        // Ensure the user has enough points (just a check, no deduction)
        if ($user->points < $request->points) {
            return response()->json([
                'message' => 'Insufficient points. Please purchase more points to create an adboard.'
            ], 400);
        }

        // Create the Adboard without deducting points
        $adboard = new Adboard();
        $adboard->album_id = $request->album_id;
        $adboard->status = 'draft'; // Change status to 'draft' (not active yet)
        $adboard->points = $request->points;
        $adboard->budget = $request->points;
        $adboard->description = $request->description;
        $adboard->name = $request->name;
        $adboard->save();

        return response()->json([
            'message' => 'Adboard created successfully! (Not published yet)',
            'adboard' => $adboard,
            'id' => $adboard->id,
            'remaining_points' => $user->points // Return points without deduction
        ], 201);
    }


    public function adstore(Request $request)
{
    DB::beginTransaction();
    try {
        // Decode JSON fields
        $categories = json_decode($request->categories, true);
        $targetData = json_decode($request->target_data, true);

        // Validate target data structure
        if (!isset($targetData['type'])) {
            throw new \Exception("Target type is required");
        }

        // Create ad
        $ad = Ad::create([
            'adboard_id' => $request->adboard_id,
            'cta_name' => $request->cta_name,
            'cta_link' => $request->cta_link,
            'description' => $request->description,
            'status' => 'active',
            'target' => $targetData['type'], // Set target type from the data
        ]);

        // Attach categories
        $ad->categories()->attach($categories);

        // Process target data
        $this->processTargetData($ad, $targetData);

        // Process media files
        foreach ($request->media as $media) {
            $path = $media['file']->store('ads/media/original', 's3');

            $media = AdMedia::create([
                'ad_id' => $ad->id,
                'file_path' => $path,
                'sequence_order' => $media['sequence_order'],
                'status' => 'active',
                'type' => 'active',
            ]);
            AdImageCompress::dispatch($media->fresh());
        }

        DB::commit();

        return response()->json(['id' => $ad->id], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


protected function processTargetData(Ad $ad, array $targetData)
{
    Log::debug('Processing target data', ['target_type' => $targetData['type'] ?? 'none']);

    if ($targetData['type'] === 'all_region') {
        Log::debug('Creating all-region target');
        $target = $ad->targets()->create([
            'continent' => null,
            'country' => null,
            'status' => 'active',
        ]);
        Log::info('All-region target created', ['target_id' => $target->id]);
    } else {
        // Process continents if they exist
        if (!empty($targetData['continents'])) {
            Log::debug('Processing continent targets', ['count' => count($targetData['continents'])]);
            foreach ($targetData['continents'] as $continent) {
                $target = $ad->targets()->create([
                    'continent' => $continent,
                    'country' => null,
                    'status' => 'active',
                ]);
                Log::debug('Continent target created', [
                    'target_id' => $target->id,
                    'continent' => $continent
                ]);
            }
        }

        // Process countries if they exist
        if (!empty($targetData['countries'])) {
            Log::debug('Processing country targets', ['count' => count($targetData['countries'])]);
            foreach ($targetData['countries'] as $country) {
                $continent = $this->getContinentForCountry($country);
                $target = $ad->targets()->create([
                    'country' => $country,
                    'continent' => $continent,
                    'status' => 'active',
                ]);
                Log::debug('Country target created', [
                    'target_id' => $target->id,
                    'country' => $country,
                    'continent' => $continent
                ]);
            }
        }
    }
    Log::info('Target data processing completed');
}
    protected function getContinentForCountry(string $country): ?string
    {
        $continentMap = [
            'Nigeria' => 'Africa',
            'Kenya' => 'Africa',
            'South Africa' => 'Africa',
            'India' => 'Asia',
            'China' => 'Asia',
            'Japan' => 'Asia',
            'Germany' => 'Europe',
            'France' => 'Europe',
            'UK' => 'Europe',
            'USA' => 'North America',
            'Canada' => 'North America',
            'Mexico' => 'North America',
            'Brazil' => 'South America',
            'Argentina' => 'South America',
            'Chile' => 'South America',
        ];

        $continent = $continentMap[$country] ?? null;

        if (!$continent) {
            Log::warning('Continent not found for country', ['country' => $country]);
        }

        return $continent;
    }

    public function show($id)
    {
        try {
            $ad = Ad::with(['media', 'adboard.album', 'categories', 'targets'])
                ->findOrFail($id);

            // Get the album details via adboard
            $album = $ad->adboard->album ?? null;

            // Get the number of supporters for the album
            $supportersCount = $album ? $album->supporters()->count() : 0;

            // Default profile image
            $defaultProfile = asset('images/default-profile.png');

            // Determine the profile image based on album type
            $profileUrl = $defaultProfile;

            if ($album) {
                if (in_array($album->type, ['personal', 'creator'])) {
                    $profileUrl = $album->thumbnail_compressed
                        ? Storage::disk('s3')->url($album->thumbnail_compressed)
                        : ($album->thumbnail_original
                            ? Storage::disk('s3')->url($album->thumbnail_original)
                            : $defaultProfile);
                } elseif ($album->type === 'business') {
                    $profileUrl = $album->business_logo_compressed
                        ? Storage::disk('s3')->url($album->business_logo_compressed)
                        : ($album->business_logo_original
                            ? Storage::disk('s3')->url($album->business_logo_original)
                            : $defaultProfile);
                }
            }

            // Format the response data
            $response = [
                'id' => $ad->id,
                'title' => $ad->name ?? 'No Title',
                'description' => $ad->description,
                'cta_name' => $ad->cta_name,
                'cta_link' => $ad->cta_link,
                'status' => $ad->status,
                'created_at' => $ad->created_at->toDateTimeString(),
                'creator' => [
                    'name' => $album->name ?? 'Unknown Album',
                    //'is_verified' => $album->is_verified ?? false,
                    'is_verified' => (bool)$album->is_verified,
                    'profile_image' => $profileUrl,
                ],
                'media_urls' => $ad->media->map(function ($media) {
                    return [
                        'url' => $media->file_path ? Storage::disk('s3')->url($media->file_path) : null,
                        'type' => $media->type,
                        'sequence_order' => $media->sequence_order,
                    ];
                })->sortBy('sequence_order')->pluck('url'),
                'supporters' => $supportersCount,
                'categories' => $ad->categories->pluck('name'),
                'target_data' => $ad->targets->map(function ($target) {
                    return [
                        'continent' => $target->continent,
                        'country' => $target->country,
                    ];
                }),
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ad not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function publish(Request $request)
    {
        DB::beginTransaction(); // Start transaction

        try {
            $user = Auth::user(); // Get the authenticated user
            $ad = Ad::findOrFail($request->ad_id);
            $adboard = $ad->adboard;

            // Ensure the user has enough points to publish the ad
            if ($user->points < $adboard->points) {
                return response()->json([
                    'message' => 'Insufficient points. Please purchase more points to publish this ad.'
                ], 400);
            }

            // Deduct points from the user
            $user->decrement('points', $adboard->points);

            // Update ad status to published
            $ad->status = 'published';
            $ad->save();

            $adboard->status = 'active';
            $adboard->save();

            DB::commit(); // Commit transaction

            return response()->json([
                'message' => 'Ad published successfully!',
                'ad' => $ad,
                'id' => $ad->id,
                'remaining_points' => $user->points // Show remaining points
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction if error occurs
            return response()->json([
                'error' => 'Failed to publish ad',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAds()
    {
        $userId = auth()->id();

         $ads = Ad::where('status', '!=', 'deleted') // ✅ Exclude deleted ads
            ->whereHas('adboard.album', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with('adboard') // Eager load adboard
            ->get();


        $adsData = $ads->map(function ($ad) {
            $impressions = AdImpression::where('ad_id', $ad->id)->count();
            $clicks = AdClick::where('ad_id', $ad->id)->count();

            $ctr = $impressions > 0 ? number_format(($clicks / $impressions) * 100, 1) : 0;

            return [
                'id' => 'AD-' . str_pad($ad->id, 3, '0', STR_PAD_LEFT),
                'adid' => (String) $ad->id,
                'name' => $ad->adboard->name ?? 'Unknown',
                'status' => ucfirst($ad->adboard->status),
                'budget' => $ad->adboard->budget ?? 0,
                'impressions' => (String) $impressions,
                'clicks' => (String) $clicks,
                'ctr' => $ctr,
                'start_date' => $ad->created_at->toDateString(),
                'end_date' => 'Ongoing', // adjust if you have real end date
            ];
        });

        return response()->json([
            'ads' => $adsData
        ]);
    }

    public function getAdPerformance($adId)
    {
        $ad = Ad::findOrFail($adId);

        $start = Carbon::parse($ad->created_at)->startOfDay();
        $end = Carbon::today();

        $dailyData = [];

        while ($start->lte($end)) {
            $date = $start->toDateString(); // Get date as "Y-m-d"

            // Get daily clicks and points used
            $clicks = DB::table('ad_clicks')
                ->where('ad_id', $adId)
                ->whereDate('created_at', $date)
                ->count();

            $totalClickPoints = DB::table('ad_clicks')
                ->where('ad_id', $adId)
                ->whereDate('created_at', $date)
                ->sum('points_used'); // Assuming points_used is a field in the ad_clicks table

            // Get daily impressions and points used
            $impressions = DB::table('ad_impressions')
                ->where('ad_id', $adId)
                ->whereDate('created_at', $date)
                ->count();

            $totalImpressionPoints = DB::table('ad_impressions')
                ->where('ad_id', $adId)
                ->whereDate('created_at', $date)
                ->sum('points_used'); // Assuming points_used is a field in the ad_impressions table

            // Calculate cost using points (you can adjust the cost-per-point logic)
            $cost = ($totalClickPoints + $totalImpressionPoints); // Example: $0.01 per point, adjust as needed

            // Calculate CTR (Click-Through Rate) for the day
            $ctr = ($impressions > 0) ? ($clicks / $impressions) * 100 : 0;

            // Format the date as "Jun, 21"
            $formattedDate = $start->format('M, d');

            // Add the daily data to the array
            $dailyData[] = [
                'date' => $formattedDate,
                'clicks' => (String) $clicks,
                'impressions' => (String) $impressions,
                'cost' => (String) $cost, // Round the cost to 2 decimal places
                'ctr' => number_format($ctr, 0),  // Round the CTR to 2 decimal places
            ];

            $start->addDay(); // Move to the next day
        }

        $impressionscount = AdImpression::where('ad_id', $ad->id)->count();
        $clickscount = AdClick::where('ad_id', $ad->id)->count();
        // General metrics (overall CTR, cost-per-click, conversion rate)
        $ctr = ($impressionscount > 0) ? ($clickscount / $impressionscount) * 100 : 0;
        $costPerClick = ($ad->clicks > 0) ? ($ad->total_spent / $ad->clicks) : 0;

        $conversions = DB::table('ad_cta_clicks')
        ->where('ad_id', $ad->id)
        ->count();

        $conversionRate = ($clickscount > 0) ? ($conversions / $clickscount) * 100 : 0;

        $total_spent = $ad->adboard->budget - $ad->adboard->points;

        $album = $ad->adboard->album ?? null;
        $defaultProfile = asset('images/default-profile.png');

         $profileUrl = $defaultProfile;

                if ($album) {
                    if (in_array($album->type, ['personal', 'creator'])) {
                        $profileUrl = $album->thumbnail_compressed
                            ? Storage::disk('s3')->url($album->thumbnail_compressed)
                            : ($album->thumbnail_original
                                ? Storage::disk('s3')->url($album->thumbnail_original)
                                : $defaultProfile);
                    } elseif ($album->type === 'business') {
                        $profileUrl = $album->business_logo_compressed
                            ? Storage::disk('s3')->url($album->business_logo_compressed)
                            : ($album->business_logo_original
                                ? Storage::disk('s3')->url($album->business_logo_original)
                                : $defaultProfile);
                    }
                }

        return response()->json([
            'ad_id' => $ad->id,
            'ad_name' => $ad->adboard->name,
            'album_name' => $ad->adboard->album->name,
            'album_logo_url' => $profileUrl,
            'status' => $ad->adboard->status,
            'budget' => $ad->adboard->budget,
            'total_spent' => (String) $total_spent,
            'start_date' => $ad->created_at,
            'end_date' => $ad->end_date,
            'impressions' => (String) $impressionscount,
            'clicks' => (String) $clickscount,
            'conversions' => (String) $conversions,
            'ctr' => number_format($ctr, 0),
            'cost_per_click' => '2',
            'conversion_rate' => number_format($conversionRate, 0),
            'daily_performance' => $dailyData, // This contains all the days from start to today
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,paused'
        ]);

        DB::beginTransaction();

        try {
            // Find the ad and ensure it exists
            $ad = Ad::findOrFail($id);

            // Find its related adboard
            $adboard = Adboard::findOrFail($ad->adboard_id);

            // Check if the authenticated user owns the album
            if ($adboard->album->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Unauthorized: You do not own this ad\'s album.'
                ], 403);
            }

            // Check if status is actually changing
            if ($ad->status === $request->status && $adboard->status === $request->status) {
                return response()->json([
                    'message' => 'Ad is already ' . $request->status,
                ], 200);
            }

            // Update both ad and adboard statuses
            $ad->status = $request->status;
            $ad->save();

            $adboard->status = $request->status;
            $adboard->save();

            DB::commit();

            return response()->json([
                'message' => 'Ad and Adboard status updated successfully',
                'data' => [
                    'id' => $ad->id,
                    'adboard_id' => $adboard->id,
                    'new_status' => $request->status
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update ad status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   public function deleteAd($id)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            $ad = Ad::where('id', $id)->firstOrFail();
            $adboard = Adboard::where('id', $ad->adboard_id)->firstOrFail();

            // Check ownership
            $album = Album::where('id', $adboard->album_id)->where('user_id', $user->id)->firstOrFail();

            // Get points to refund
            $pointsToRefund = $adboard->points;

            // Refund points
            $balanceBefore = $user->points;
            $user->points += $pointsToRefund;
            $user->save();

            // Clear adboard points
            $adboard->points = 0;
            $adboard->save();

            // Mark ad as deleted
            $ad->status = 'deleted';
            $ad->save();

            // Also update adboard status
            $adboard->status = 'deleted';
            $adboard->save();

            // Record transaction
            PointTransaction::create([
                'user_id' => $user->id,
                'resource_id' => $ad->id,
                'points' => $pointsToRefund,
                'type' => 'ad_points_refund',
                'status' => 'completed',
                'balance_after' => $user->points,
                'description' => "Refunded $pointsToRefund points from deleted ad",
                'metadata' => json_encode([
                    'ad_id' => $ad->id,
                    'adboard_id' => $adboard->id,
                    'points_before_refund' => $pointsToRefund,
                ])
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Ad deleted successfully and points refunded',
                'refunded_points' => $pointsToRefund,
                'new_balance' => $user->points
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete ad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function adboardedit($id)
    {
        $ad = Ad::where('id', $id)->firstOrFail();
        $adBoard = AdBoard::with('album')->findOrFail($ad->adboard_id);

        // Authorization check - ensure user owns this ad board
        if (auth()->id() !== $adBoard->album->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'adboard' => [
                'id' => $adBoard->id,
                'name' => $adBoard->name,
                'description' => $adBoard->description,
                'points' => $adBoard->points,
                'status' => $adBoard->status,
                'album_id' => $adBoard->album_id,
                'album_name' => $adBoard->album->name,
                'created_at' => $adBoard->created_at,
            ]
        ]);
    }

    public function updateju(Request $request, $id)
    {
        $ad = Ad::findOrFail($id);
        $adBoard = AdBoard::findOrFail($ad->adboard_id);
        $album = Album::findOrFail($adBoard->album_id);

        // Authorization check
        if (auth()->id() !== $album->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive',
            'album_id' => 'required|exists:albums,id',
        ]);

        // Check if points are being increased
        $pointsDifference = $validated['points'] - $adBoard->points;
        if ($pointsDifference > 0) {
            $user = auth()->user();
            if ($user->available_points < $pointsDifference) {
                return response()->json([
                    'message' => 'Not enough points available'
                ], 422);
            }

            // Deduct the additional points
            $user->decrement('available_points', $pointsDifference);
        } else if ($pointsDifference < 0) {
            // If points are being decreased, refund the difference
            $user = auth()->user();
            $user->increment('available_points', abs($pointsDifference));
        }

        $adBoard->update($validated);

        $adBoard->status = 'review';
        $adBoard->save();
        return response()->json([
            'message' => 'Ad board updated successfully',
            'adboard' => $adBoard,
            'ad_id' => $ad->id
        ]);
    }

    public function update(Request $request, $id)
{
    $ad = Ad::findOrFail($id);
    $adBoard = AdBoard::findOrFail($ad->adboard_id);
    $album = Album::findOrFail($adBoard->album_id);

    // Authorization check
    if (auth()->id() !== $album->user_id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'points' => 'required|integer|min:1',
        'status' => 'required|in:active,inactive',
        'album_id' => 'required|exists:albums,id',
    ]);

    $user = auth()->user();
    $pointsDifference = $validated['points'] - $adBoard->points;

    if ($pointsDifference > 0) {
        if ($user->points < $pointsDifference) {
            return response()->json([
                'message' => 'Not enough points available'
            ], 422);
        }

        $user->decrement('points', $pointsDifference);

        PointTransaction::create([
            'user_id' => $user->id,
            'resource_id' => $ad->id,
            'points' => -$pointsDifference,
            'type' => 'ad_points_allocation',
            'status' => 'completed',
            'balance_after' => $user->fresh()->points,
            'description' => "Allocated $pointsDifference additional points to ad",
            'metadata' => json_encode([
                'ad_id' => $ad->id,
                'adboard_id' => $adBoard->id,
                'previous_points' => $adBoard->points,
                'new_points' => $validated['points'],
            ])
        ]);
    } elseif ($pointsDifference < 0) {
        $refundAmount = abs($pointsDifference);
        $user->increment('points', $refundAmount);

        PointTransaction::create([
            'user_id' => $user->id,
            'resource_id' => $ad->id,
            'points' => $refundAmount,
            'type' => 'ad_points_refund',
            'status' => 'completed',
            'balance_after' => $user->fresh()->points,
            'description' => "Refunded $refundAmount points from ad",
            'metadata' => json_encode([
                'ad_id' => $ad->id,
                'adboard_id' => $adBoard->id,
                'previous_points' => $adBoard->points,
                'new_points' => $validated['points'],
            ])
        ]);
    }

    $adBoard->update($validated);
    $adBoard->status = 'review';
    $adBoard->save();

    return response()->json([
        'message' => 'Ad board updated successfully',
        'adboard' => $adBoard,
        'ad_id' => $ad->id
    ]);
}

    public function editads($id)
    {
        $ad = Ad::with(['categories', 'media', 'targets'])->findOrFail($id);

        // Authorization check
        //$this->authorize('update', $ad);

        // Group targets by type
        $continents = [];
        $countries = [];

        foreach ($ad->targets as $target) {
            if ($target->country) {
                $countries[] = $target->country;
            } elseif ($target->continent) {
                $continents[] = $target->continent;
            }
        }

        return response()->json([
            'ad' => [
                'id' => $ad->id,
                'cta_name' => $ad->cta_name,
                'cta_link' => $ad->cta_link,
                'description' => $ad->description,
            'target' => $ad->target ?? 'all',
            ],
            'categories' => $ad->categories->pluck('id')->toArray(),
            'media' => $ad->media->map(function($media) {
                return [
                    'id' => $media->id,
                    'file_path' => Storage::disk('s3')->url($media->file_path),
                    'sequence_order' => $media->sequence_order,
                ];
            })->toArray(),
            'targets' => [
                'continents' => array_unique($continents),
                'countries' => array_unique($countries),
            ]
        ]);
    }

    public function adupdate(Request $request, $id)
{
    DB::beginTransaction();
    try {
        $ad = Ad::findOrFail($id);

        $adboard = Adboard::findOrFail($ad->adboard_id);
        // Decode JSON fields
        $categories = json_decode($request->categories, true);
        $targetData = json_decode($request->target_data, true);

        // Update ad with validated data
        $ad->update([
            'cta_name' => $request->cta_name,
            'cta_link' => $request->cta_link,
            'description' => $request->description,
            'target' => $targetData['target'] ?? 'all',
            'status' => 'review',
        ]);

        $adboard->update([
            'status' => 'review',
        ]);

        // Sync categories
        $ad->categories()->sync($categories);

        // Process target data
        $ad->targets()->delete();
        $this->processTargetData($ad, $targetData);

        // Process media files if they exist in request
        $mediaToKeep = [];

        if ($request->has('media')) {
            foreach ($request->media as $index => $media) {
                $sequenceOrder = $index + 1;

                if (isset($media['id'])) {
                    // Update existing media sequence
                    AdMedia::where('id', $media['id'])
                        ->update(['sequence_order' => $sequenceOrder]);
                    $mediaToKeep[] = $media['id'];
                } else {
                    // Validate new media file
                    if (!isset($media['file'])) {
                        throw new \Exception('Media file is required');
                    }

                    $path = $media['file']->store('ads/media/original', 's3');

                    $newMedia = AdMedia::create([
                        'ad_id' => $ad->id,
                        'file_path' => $path,
                        'sequence_order' => $sequenceOrder,
                        'status' => 'active',
                        'type' => 'active',
                    ]);
                    AdImageCompress::dispatch($newMedia->fresh());
                    $mediaToKeep[] = $newMedia->id;
                }
            }

            // Delete any media not included in the update
            $ad->media()
               ->whereNotIn('id', $mediaToKeep)
               ->delete();
        }

        DB::commit();
        return response()->json([
            'message' =>'Ad updated successfully',
            'id' => $ad->id,
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}
