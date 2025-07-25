<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Template;
use App\Jobs\TemplateGenAI;
use App\Jobs\TemplateCreate;
use Illuminate\Http\Request;
use App\Models\PointTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 10;
        $userId = Auth::user()->id;

        // Get templates that are either public (type 'open') or owned by the user
        $templates = Template::where('status', 'completed')
            ->where(function($query) use ($userId) {
                $query->where('type', 'open')
                    ->orWhere('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Include user points in response
        $userPoints = Auth::user()->points;
        $pointgen = '60';

        $transformed = $templates->getCollection()->map(function ($template) use ($userId) {
            return [
                'id' => $template->id,
                'name' => $template->name,
                'type' => $template->type,
                'path' => generateSecureMediaUrl($template->compressed_template ?? $template->original_template),
                'is_user_generated' => $template->user_id == $userId,
                'created_at' => $template->created_at->toIso8601String(),
                'is_new' => $template->created_at->gt(now()->subHours(24))
            ];
        });

        return response()->json([
            'templates' => $transformed,
            'pagination' => [
                'current_page' => $templates->currentPage(),
                'last_page' => $templates->lastPage(),
                'total' => $templates->total(),
            ],
            'user_points' => (int) $userPoints,
            'point_gen' => (int) $pointgen,
            'prompt_samples' => [
                "Minimalist abstract background with soft pastel colors and space for text",
                "Professional corporate template with geometric patterns in blue tones",
                "Vibrant gradient background with modern design elements",
                "Nature-inspired template with leaves and organic shapes",
                "Dark mode template with neon accents and tech elements"
            ]
        ]);
    }

    public function generateTemplate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'description' => 'required|min:20',
            'style' => 'sometimes|string'
        ]);

        $template_points = 40;

        if ($user->points < $template_points) {
            return response()->json(['message' => 'Insufficient points'], 400);
        }

        try {
            $template = Template::create([
                'name' => 'template_' . $user->id . '_' . time(),
                'user_id' => $user->id,
                'author' => $user->name,
                'type' => 'owned',
                'status' => 'pending',
                'description' => $request->description,
            ]);

            $transaction = PointTransaction::create([
                'user_id' => $user->id,
                'points' => $template_points,
                'type' => 'template_generation',
                'resource_id' => $template->id, // or null if not ready
                'status' => 'pending',
                'description' => 'Template generation request',
                'balance_before' => $user->points,
                'balance_after' => $user->points - $template_points,
            ]);

            // Deduct points
            $user->points -= $template_points;
            $user->save();

            TemplateGenAI::dispatch($template->id, $request->description, $user->id, $transaction->id);

            return response()->json([
                'success' => true,
                'template_id' => (string) $template->id,
                'points_remaining' => $user->points
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    public function checkStatus($id)
    {
        $template = Template::findOrFail($id);
        $user = Auth::user();

        // Check if created_at is within the past hour
        $isNew = $template->created_at->gt(Carbon::now()->subHour());

        return response()->json([
            'status' => $template->status,
            'template' => $template->status === 'completed' ? [
                'id' => $template->id,
                'path' => generateSecureMediaUrl($template->compressed_template),
                'is_new' => $isNew,
                'created_at' => $template->created_at->toIso8601String()
            ] : [
                'is_new' => $isNew
            ],
            'user_points' => $user->points
        ]);
    }
}
