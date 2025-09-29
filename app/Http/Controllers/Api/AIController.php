<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\ChatMessage;

class AIController extends Controller
{
    private $available_functions = [
        'get_user_albums' => [
            'description' => 'Get all albums belonging to the user with their descriptions, types, and recent activity',
            'parameters' => [
                'type' => 'object',
                'properties' => [],
                'required' => []
            ]
        ],
        'generate_image' => [
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
        // We'll add more functions as needed
    ];

    public function createProject(Request $request)
    {
        $user = Auth::user();

        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'New Chat', // Temporary, will be updated by AI
        ]);

        // Add welcome message
        ChatMessage::create([
            'project_id' => $project->id,
            'role' => 'assistant',
            'content' => "Hi! I'm Venusnap AI, your creative assistant. I can help you with content ideas, analyze your albums, generate images, and much more. What would you like to work on today?",
        ]);

        return response()->json([
            'project' => $project,
            'messages' => $project->messages
        ]);
    }

    public function sendMessage(Request $request, $projectId)
    {
        $user = Auth::user();
        $project = Project::where('user_id', $user->id)->findOrFail($projectId);

        $request->validate([
            'message' => 'required|string'
        ]);

        // Save user message
        ChatMessage::create([
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
        return $this->processAIResponse($project, $response);
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
            'model' => 'gpt-4', // or gpt-4-turbo-preview for better function calling
            'messages' => $messages,
            'functions' => array_values($this->available_functions),
            'function_call' => 'auto',
        ])->json();
    }

    private function processAIResponse($project, $response)
    {
        $aiResponse = $response['choices'][0]['message'];

        // Check if AI wants to call a function
        if (isset($aiResponse['function_call'])) {
            return $this->handleFunctionCall($project, $aiResponse);
        }

        // Regular response - save and return
        $message = ChatMessage::create([
            'project_id' => $project->id,
            'role' => 'assistant',
            'content' => $aiResponse['content'],
        ]);

        // Auto-generate project title if this is early in conversation
        $this->generateProjectTitle($project);

        return response()->json([
            'message' => $message,
            'project' => $project->fresh()
        ]);
    }

    private function handleFunctionCall($project, $aiResponse)
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
        ChatMessage::create([
            'project_id' => $project->id,
            'role' => 'function',
            'content' => json_encode($functionResult),
            'function_name' => $functionName,
            'metadata' => ['result' => $functionResult]
        ]);

        // Continue conversation with function result
        return $this->continueConversationWithFunctionResult($project);
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
        $albums = []; // YourAlbumModel::where('user_id', $user->id)->get();

        return [
            'albums' => $albums,
            'total_count' => count($albums),
            'last_updated' => now()->toISOString()
        ];
    }

    private function generateImage($prompt)
    {
        // Use your existing image generation logic
        // This should integrate with your current ProcessArtworkImage job

        return [
            'status' => 'pending',
            'message' => 'Image generation started',
            'prompt' => $prompt
        ];
    }

    private function continueConversationWithFunctionResult($project)
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
        return $this->processAIResponse($project, $response);
    }

    private function generateProjectTitle($project)
    {
        // Generate title after 2-3 messages
        $messageCount = $project->messages()->count();

        if ($messageCount === 3) {
            // Use AI to generate title based on conversation
            // Similar to your prompt enhancement but for titles
        }
    }
}
