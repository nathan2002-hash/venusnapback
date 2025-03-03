<?php

namespace App\Http\Controllers\Api;

use App\Models\Template;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class TemplateController extends Controller
{
    public function index(Request $request)
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
}
