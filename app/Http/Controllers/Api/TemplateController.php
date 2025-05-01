<?php

namespace App\Http\Controllers\Api;

use App\Models\Template;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\PointTransaction;
use App\Jobs\TemplateCreate;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 10;
        $templates = Template::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    
        // Include user points in response
        $userPoints = auth()->user()->points;
    
        $transformed = $templates->getCollection()->map(function ($template) {
            return [
                'id' => $template->id,
                'name' => $template->name,
                'type' => $template->type,
                'path' => Storage::disk('s3')->url($template->compressed_template ?? $template->original_template), 
                'is_user_generated' => !empty($template->user_id),
            ];
        });
    
        return response()->json([
            'templates' => $transformed,
            'pagination' => [
                'current_page' => $templates->currentPage(),
                'last_page' => $templates->lastPage(),
            ],
            'user_points' => $userPoints
        ]);
    }
    public function indhex(Request $request)
    {
        $perPage = 10; // Adjust if needed
        $templates = Template::orderBy('created_at', 'desc')->paginate($perPage);
    
        // Transform the templates to fit the structure Flutter expects
        $templates->getCollection()->transform(function ($template) {
            return [
                'id' => $template->id,
                'name' => $template->name,
                'type' => $template->type, // free or premium
                'path' => Storage::disk('s3')->url($template->compressed_template ?? $template->original_template), // URL to the image
            ];
        });
    
        return response()->json($templates);
    }

    public function generateTemplate(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'description' => 'required|min:20',
            'style' => 'sometimes|string' // Optional style parameter
        ]);
    
        $template_points = 40; // Different points cost for templates
    
        $transaction = PointTransaction::create([
            'user_id' => $user->id,
            'points' => $template_points,
            'type' => 'template_generation',
            'status' => 'pending',
            'description' => 'Template generation request',
            'balance_before' => $user->points,
            'balance_after' => $user->points
        ]);
    
        if ($user->points < $template_points) {
            $transaction->update(['status' => 'failed']);
            return response()->json(['message' => 'Insufficient points'], 400);
        }
    
        try {
            $template = Template::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'original_description' => $request->description,
                'style' => $request->style ?? 'general',
                'point_transaction_id' => $transaction->id
            ]);
    
            TemplateCreate::dispatch($template->id, $request->description, $user->id, $transaction->id);
    
            return response()->json([
                'success' => true,
                'template_id' => (string) $template->id,
                'points_remaining' => $user->points - $template_points
            ]);
    
        } catch (\Exception $e) {
            $transaction->update(['status' => 'failed']);
            return response()->json(['message' => 'Failed to initiate generation'], 500);
        }
    }
}
