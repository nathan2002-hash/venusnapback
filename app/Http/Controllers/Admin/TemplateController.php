<?php

namespace App\Http\Controllers\Admin;

use App\Models\Template;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\Admin\TemplateCreate;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.template.index', [
           'templates' => $templates,
        ]);
    }

    public function create()
    {
        //$templates = Template::orderBy('created_at', 'desc')->paginate(30);
        return view('admin.template.create', [
           //'templates' => $templates,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:free,premium',
            'tempfile' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'author' => 'required|string|max:255',
        ]);
        $user = Auth::user();

        // Check if album name already exists
        if (Template::where('name', $request->name)->exists()) {
            return response()->json(['message' => 'Template name already exists'], 409);
        }

        // Save Album (without thumbnail path for now)
        $template = new Template();
        $template->user_id = $user->id;
        $template->name = $request->name;
        $template->author = $request->author;
        $template->description = $request->description;
        $template->type = $request->type; // or some default if type is missing
        if ($request->hasFile('tempfile')) {
            $path = $request->file('tempfile')->store('uploads/templates/originals', 's3');
        }
        $template->original_template = $path;
        $template->save();

        TemplateCreate::dispatch($template->id);

        return redirect('/restricted/templates');
    }
}
