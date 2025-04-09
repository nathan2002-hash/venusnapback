<?php

namespace App\Http\Controllers\Api;

use App\Models\Ad;
use Carbon\Carbon;
use App\Models\Adboard;
use App\Models\AdClick;
use App\Models\AdMedia;
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
        // Get the authenticated user's albums
        $albums = $request->user()->albums()->select('id', 'name')->get();

        // Return response in JSON format
        return response()->json([
            'albums' => $albums
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

            // Create ad
            $ad = Ad::create([
                'adboard_id' => $request->adboard_id,
                'cta_name' => $request->cta_name,
                'cta_link' => $request->cta_link,
                'description' => $request->description,
                'status' => 'active',
                'target' => 'all_region',
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
        Log::debug('Processing target data', ['target_type' => $targetData['target'] ?? 'none']);

        if ($targetData['target'] === 'all_region') {
            Log::debug('Creating all-region target');
            $target = $ad->targets()->create([
                'continent' => null,
                'country' => null,
                'status' => 'active',
            ]);
            Log::info('All-region target created', ['target_id' => $target->id]);
        } else {
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
                    'is_verified' => $album->is_verified ?? false,
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
        $ads = Ad::whereHas('adboard.album', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->with('adboard') // eager load adboard
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
            'ctr' => number_format($ctr, 2),  // Round the CTR to 2 decimal places
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

    return response()->json([
        'ad_id' => $ad->id,
        'ad_name' => $ad->adboard->name,
        'status' => $ad->adboard->status,
        'budget' => $ad->adboard->budget,
        'total_spent' => (String) $total_spent,
        'start_date' => $ad->created_at,
        'end_date' => $ad->end_date,
        'impressions' => (String) $impressionscount,
        'clicks' => (String) $clickscount,
        'conversions' => (String) $conversions,
        'ctr' => number_format($ctr, 2),
        'cost_per_click' => '2',
        'conversion_rate' => number_format($conversionRate, 2),
        'daily_performance' => $dailyData, // This contains all the days from start to today
    ]);
}
}
