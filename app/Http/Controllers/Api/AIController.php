<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\ChatMessage;

class AIController extends Controller
{
     private $available_functions = [
        'get_user_albums' => [
            'name' => 'get_user_albums',
            'description' => 'Get all albums belonging to the user with their descriptions, types, and recent activity',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
                'required' => []
            ]
        ],
        'generate_image' => [
            'name' => 'generate_image',
            'description' => 'Generate an image based on a detailed prompt for social media content',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'prompt' => [
                        'type' => 'string',
                        'description' => 'Detailed description of the image to generate'
                    ]
                ],
                'required' => ['prompt']
            ]
        ]
    ];

    // Get all projects for the user
    public function getProjects(Request $request)
    {
        $user = $request->user();

        $projects = Project::where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'projects' => $projects
        ]);
    }

    public function createProject(Request $request)
    {
        $user = $request->user();

        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'New Chat',
        ]);

        // Add welcome message
        ChatMessage::create([
            'project_id' => $project->id,
            'role' => 'assistant',
            'content' => "Hi! I'm Venusnap AI, your creative assistant. I can help you with content ideas, analyze your albums, generate images, and much more. What would you like to work on today?",
        ]);

        return response()->json([
            'project' => $project->load('messages'),
            'messages' => $project->messages
        ]);
    }

    public function getMessages(Request $request, $projectId)
    {
        $user = $request->user();
        $project = Project::where('user_id', $user->id)->findOrFail($projectId);

        $messages = $project->messages()->orderBy('created_at', 'asc')->get();

        return response()->json([
            'messages' => $messages
        ]);
    }

    public function sendMessage(Request $request, $projectId)
    {
        $user = $request->user();
        $project = Project::where('user_id', $user->id)->findOrFail($projectId);

        $request->validate([
            'message' => 'required|string'
        ]);

        // Save user message
        $userMessage = ChatMessage::create([
            'project_id' => $project->id,
            'role' => 'user',
            'content' => $request->message,
        ]);

        // Get conversation history (last 15 messages for context)
        $messages = $this->prepareConversationHistory($project);

        // Add system prompt
        array_unshift($messages, [
            'role' => 'system',
            'content' => $this->getSystemPrompt()
        ]);

        // Call OpenAI with function calling
        $response = $this->callOpenAIWithFunctions($messages);

        // Process the response and handle function calls
        return $this->processAIResponse($project, $response, $userMessage);
    }

    public function renameProject(Request $request, $projectId)
    {
        $user = $request->user();
        $project = Project::where('user_id', $user->id)->findOrFail($projectId);

        $request->validate([
            'title' => 'required|string|max:255'
        ]);

        $project->update([
            'title' => $request->title
        ]);

        return response()->json([
            'project' => $project
        ]);
    }

    public function deleteProject(Request $request, $projectId)
    {
        $user = $request->user();
        $project = Project::where('user_id', $user->id)->findOrFail($projectId);

        // Delete all messages first
        ChatMessage::where('project_id', $project->id)->delete();

        // Then delete the project
        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully'
        ]);
    }

    public function checkImageStatus(Request $request, $imageId)
    {
        // Implement your image status checking logic here
        // This would check your Artwork model status

        return response()->json([
            'status' => 'completed', // or 'pending', 'failed'
            'image_url' => null // Add actual image URL when ready
        ]);
    }

    private function prepareConversationHistory($project)
    {
        $messages = [];

        $chatMessages = $project->messages()
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get()
            ->reverse();

        foreach ($chatMessages as $msg) {
            $message = [
                'role' => $msg->role,
                'content' => $msg->content
            ];

            if ($msg->function_name) {
                $message['name'] = $msg->function_name;
            }

            $messages[] = $message;
        }

        return $messages;
    }

    private function getSystemPrompt()
    {
        return "You are Venusnap AI, an intelligent creative assistant for social media content creation.

You help creators with:
- Content strategy and ideas
- Album management and recommendations
- Image generation and creative direction
- Social media best practices

You have access to tools that let you fetch real user data when needed. Be conversational, creative, and proactive.

When you need information about the user's albums, analytics, or want to generate images, use the available functions.";
    }

    private function callOpenAIWithFunctions($messages)
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4', // or gpt-4-turbo-preview
            'messages' => $messages,
            'functions' => array_values($this->available_functions),
            'function_call' => 'auto',
        ])->json();
    }

    private function processAIResponse($project, $response, $userMessage)
    {
        $aiResponse = $response['choices'][0]['message'];

        // Check if AI wants to call a function
        if (isset($aiResponse['function_call'])) {
            return $this->handleFunctionCall($project, $aiResponse, $userMessage);
        }

        // Regular response - save and return
        $assistantMessage = ChatMessage::create([
            'project_id' => $project->id,
            'role' => 'assistant',
            'content' => $aiResponse['content'],
        ]);

        // Auto-generate project title if this is early in conversation
        $this->generateProjectTitle($project);

        return response()->json([
            'userMessage' => $userMessage,
            'newMessages' => [$assistantMessage],
            'project' => $project->fresh()
        ]);
    }

    private function handleFunctionCall($project, $aiResponse, $userMessage)
    {
        $functionCall = $aiResponse['function_call'];
        $functionName = $functionCall['name'];
        $arguments = json_decode($functionCall['arguments'], true);

        // Save the function call request
        $functionMessage = ChatMessage::create([
            'project_id' => $project->id,
            'role' => 'assistant',
            'content' => null,
            'function_name' => $functionName,
            'metadata' => ['arguments' => $arguments]
        ]);

        // Execute the function
        $functionResult = $this->executeFunction($functionName, $arguments);

        // Save function result
        $functionResultMessage = ChatMessage::create([
            'project_id' => $project->id,
            'role' => 'function',
            'content' => json_encode($functionResult),
            'function_name' => $functionName,
            'metadata' => ['result' => $functionResult]
        ]);

        // Continue conversation with function result
        return $this->continueConversationWithFunctionResult($project, $userMessage);
    }

    private function executeFunction($functionName, $arguments)
    {
        switch ($functionName) {
            case 'get_user_albums':
                return $this->getUserAlbums();

            case 'generate_image':
                return $this->generateImage($arguments['prompt']);

            default:
                return ['error' => 'Function not implemented'];
        }
    }

    private function getUserAlbums()
    {
        $user = Auth::user();

        // Replace with your actual album fetching logic
        // Example:
        $albums = Album::where('user_id', $user->id)
            ->withCount('posts')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($album) {
                return [
                    'id' => $album->id,
                    'name' => $album->name,
                    'type' => $album->type,
                    'post_count' => $album->posts->count(),
                    'last_activity' => $album->updated_at->diffForHumans()
                ];
            });

        $albums = []; // Temporary empty array

        return [
            'albums' => $albums,
            'total_count' => count($albums),
            'last_updated' => now()->toISOString()
        ];
    }

    private function generateImage($prompt)
    {
        $user = Auth::user();

        // Use your existing image generation logic from your other controller
        // This would integrate with your ProcessArtworkImage job

        // For now, return a simulated response
        return [
            'status' => 'pending',
            'message' => 'Image generation started',
            'prompt' => $prompt,
            'estimated_completion_time' => '30 seconds'
        ];

        // Later, integrate with your actual image generation:
        // $artwork = Artwork::create([...]);
        // ProcessArtworkImage::dispatch($artwork->id, $prompt, $user->id, $transaction->id);
        // return ['status' => 'pending', 'artwork_id' => $artwork->id];
    }

    private function continueConversationWithFunctionResult($project, $userMessage)
    {
        // Get updated conversation history including function call and result
        $messages = $this->prepareConversationHistory($project);
        array_unshift($messages, [
            'role' => 'system',
            'content' => $this->getSystemPrompt()
        ]);

        // Call OpenAI again with the function result
        $response = $this->callOpenAIWithFunctions($messages);

        // Process the final response
        $aiResponse = $response['choices'][0]['message'];

        $assistantMessage = ChatMessage::create([
            'project_id' => $project->id,
            'role' => 'assistant',
            'content' => $aiResponse['content'],
        ]);

        return response()->json([
            'userMessage' => $userMessage,
            'newMessages' => [$assistantMessage],
            'project' => $project->fresh()
        ]);
    }

    private function generateProjectTitle($project)
    {
        // Generate title after 2-3 messages
        $messageCount = $project->messages()->count();

        if ($messageCount === 3) {
            // Use AI to generate title based on conversation
            // You can implement this later
        }
    }
}
