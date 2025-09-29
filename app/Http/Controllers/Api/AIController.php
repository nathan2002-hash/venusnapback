<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Artwork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\ChatMessage;
use App\Jobs\ProcessArtworkImage;
use App\Models\PointTransaction;

class AIController extends Controller
{
   private $available_functions = [
        'get_user_albums' => [
            'name' => 'get_user_albums',
            'description' => 'Get all albums belonging to the user with their descriptions, types, and recent activity',
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

        $messages = $project->messages()
            ->where(function($query) {
                $query->whereNull('function_name') // Exclude function calls
                    ->orWhere('role', 'user')    // Always include user messages
                    ->orWhere('role', 'assistant'); // Include assistant messages without function calls
            })
            ->where('role', '!=', 'function') // Exclude function results
            ->orderBy('created_at', 'asc')
            ->get();

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

        // Get conversation history
        $messages = $this->prepareConversationHistory($project);

        // Add system prompt
        array_unshift($messages, [
            'role' => 'system',
            'content' => $this->getSystemPrompt()
        ]);

        try {
            // Call OpenAI with function calling
            $response = $this->callOpenAIWithFunctions($messages);

            // Check if OpenAI returned an error
            if (isset($response['error'])) {
                throw new \Exception($response['error']);
            }

            // Process the response
            return $this->processAIResponse($project, $response, $userMessage);

        } catch (\Exception $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());

            // Save error message
            $errorMessage = ChatMessage::create([
                'project_id' => $project->id,
                'role' => 'assistant',
                'content' => "I apologize, but I'm having trouble processing your request right now. Please try again in a moment.",
            ]);

            return response()->json([
                'userMessage' => $userMessage,
                'newMessages' => [$errorMessage],
                'project' => $project->fresh()
            ], 500);
        }
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
        $artwork = Artwork::findOrFail($imageId);

        return response()->json([
            'status' => $artwork->status,
            'image_url' => generateSecureMediaUrl($artwork->thumbnail),
            'prompt' => $artwork->prompt
        ]);
    }

    private function prepareConversationHistory($project)
    {
        $messages = [];

        $chatMessages = $project->messages()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->reverse();

        foreach ($chatMessages as $msg) {
            $message = ['role' => $msg->role];

            if ($msg->role === 'assistant' && $msg->function_name) {
                // Assistant is requesting a function call
                $message['function_call'] = [
                    'name' => $msg->function_name,
                    'arguments' => json_encode($msg->metadata['arguments'] ?? [])
                ];
                $message['content'] = $msg->content ?? '';
            } elseif ($msg->role === 'function') {
                // Function result coming back
                $message['name'] = $msg->function_name;
                $message['content'] = $msg->content ?? '{}';
            } else {
                // Normal user/assistant chat
                $message['content'] = $msg->content ?? '';
            }

            $messages[] = $message;
        }

        return $messages;
    }

private function getSystemPrompt()
{
    return "You are Venusnap AI, an intelligent creative assistant for social media content creation.

        ROLE: You're a helpful, creative partner - not a database reporter.

        KEY PRINCIPLES:
        1. **Be conversational** - Talk like a human creative partner, not a robot
        2. **Be insightful** - Don't just list data, provide insights and suggestions
        3. **Be concise** - Give overviews first, details only when asked
        4. **Be proactive** - Suggest next steps and creative ideas

        EXAMPLE CONVERSATION FLOW:
        USER: \"How many albums do I have?\"
        AI: \"You have 22 albums with a great mix! Your 'Pixel Pulse' is recently active, and 'Family Vault' is your most popular. Need insights on any specific album?\"

        USER: \"What do you suggest I do?\"
        AI: \"For 'Pixel Pulse', since it's your recent focus, I suggest trying trending visual content or engagement strategies. For 'StoreFinds' which has been quiet, maybe a revival campaign? Which direction interests you?\"

        USER: \"What post can I do for Pixel Pulse?\"
        AI: \"Great choice! For Pixel Pulse, consider: 1) Behind-the-scenes creative process 2) Interactive polls about design trends 3) Client success stories. Which of these resonates with your audience?\"

        CONVERSATION FLOW MANAGEMENT:
        - **First album question**: Provide overview with key insights
        - **Follow-up questions**: Focus ONLY on what was asked, don't re-list all albums
        - **Topic change**: If user changes topic, don't bring back old album data unless relevant
        DATA PRESENTATION RULES:
        - NEVER dump raw data lists unless specifically asked
        - Give summaries with interesting observations
        - Highlight patterns and opportunities
        - Suggest creative next steps
        - Focus on what's meaningful and actionable

        EXAMPLE BAD RESPONSE:
        \"You have 22 albums. Album 1: Pixel Pulse, Type: Creator, Posts: 6, Last activity: 1 week ago...\"
        \"As an AI, I don't have feelings, but...\"
        \"I cannot experience emotions, however...\"
        \"As a language model, I...\"
        BAD PATTERNS TO AVOID:
        Repeating \"you have 22 albums\" in every response
        Re-listing all album stats when user asks a specific question
        Starting over instead of building on previous conversation
        Data dumping instead of focused suggestions

        EXAMPLE GOOD RESPONSES:
        \"You have a vibrant collection of 22 albums! I notice you're quite active with your 'Pixel Pulse' creator album (updated last week), and you have a great mix of personal and professional content. Your 'Family Vault' is your most active album with 23 posts! Want me to help you organize or get ideas for any of these?\"

        \"Looking at your 22 albums, I see you have a nice balance between personal memories and creator content. Your 'Entrepreneur's Edge' album has the most posts at 16 - seems like you're building quite the business presence! Need help planning content for any specific album?\"

        Remember: You're a creative assistant, not a data reporter. Focus on insights, not just information.";
}

    private function callOpenAIWithFunctions($messages)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4',
                'messages' => $messages,
                'functions' => array_values($this->available_functions),
                'function_call' => 'auto',
            ]);

            if ($response->failed()) {
                Log::error('OpenAI API HTTP Error: ' . $response->body());
                return ['error' => 'OpenAI API request failed: ' . $response->status()];
            }

            $data = $response->json();

            if (!isset($data['choices'][0])) {
                Log::error('OpenAI API Invalid Response: ' . json_encode($data));
                return ['error' => 'Invalid response from OpenAI API'];
            }

            return $data;

        } catch (\Exception $e) {
            Log::error('OpenAI API Exception: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    private function processAIResponse($project, $response, $userMessage)
    {
        if (isset($response['error'])) {
            throw new \Exception($response['error']);
        }

        $aiResponse = $response['choices'][0]['message'];

        // Check if AI wants to call a function
        if (isset($aiResponse['function_call'])) {
            return $this->handleFunctionCall($project, $aiResponse, $userMessage);
        }

        // Regular text response
        $assistantMessage = ChatMessage::create([
            'project_id' => $project->id,
            'role' => 'assistant',
            'content' => $aiResponse['content'] ?? 'I apologize, but I encountered an error processing your request.',
        ]);

        // Auto-generate project title after a few messages
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

        $albums = Album::where('user_id', $user->id)
            ->withCount('posts')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($album) {
                return [
                    'id' => $album->id,
                    'name' => $album->name,
                    'type' => $album->type,
                    'post_count' => $album->posts_count,
                    'last_activity' => $album->updated_at->diffForHumans()
                ];
            })->toArray();

        return [
            'albums' => $albums,
            'total_count' => count($albums),
            'last_updated' => now()->toISOString()
        ];
    }

    private function generateImage($prompt)
    {
        $user = Auth::user();



        try {
            // Create artwork record
            $artwork = Artwork::create([
                'user_id' => $user->id,
                'prompt' => $prompt,
                'status' => 'pending',
            ]);

            $transaction = PointTransaction::create([
                'user_id' => $user->id,
                'points' => 30,
                'type' => 'image_regeneration',
                'resource_id' => $artwork->id,
                'status' => 'pending',
                'description' => 'Attempt to regenerate image',
                'balance_before' => $user->points,
                'balance_after' => $user->points
            ]);

            // Dispatch image generation job (use your existing job)
            ProcessArtworkImage::dispatch($artwork->id, $prompt, $user->id, $transaction->id);

            return [
                'status' => 'pending',
                'artwork_id' => $artwork->id,
                'message' => 'Image generation started',
                'prompt' => $prompt,
                'estimated_completion_time' => '30 seconds'
            ];

        } catch (\Exception $e) {
            Log::error('Image generation error: ' . $e->getMessage());
            return [
                'status' => 'failed',
                'error' => 'Failed to start image generation'
            ];
        }
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

        // Check if this is an image generation response
        $metadata = $this->extractImageMetadata($aiResponse['content']);

        $assistantMessage = ChatMessage::create([
            'project_id' => $project->id,
            'role' => 'assistant',
            'content' => $aiResponse['content'],
            'image_url' => $metadata['image_url'] ?? null,
            'image_prompt' => $metadata['image_prompt'] ?? null,
            'is_generating_image' => $metadata['is_generating'] ?? false,
        ]);

        return response()->json([
            'userMessage' => $userMessage,
            'newMessages' => [$assistantMessage],
            'project' => $project->fresh()
        ]);
    }

    private function extractImageMetadata($content)
    {
        // This method would extract image-related metadata from the AI response
        // For now, return empty - you can enhance this based on your needs
        return [];
    }

    private function generateProjectTitle($project)
    {
        // Generate title after 2-3 messages
        $messageCount = $project->messages()->count();

        if ($messageCount === 3) {
            // Use the first user message as title, or generate one with AI
            $firstUserMessage = $project->messages()
                ->where('role', 'user')
                ->orderBy('created_at', 'asc')
                ->first();

            if ($firstUserMessage && $project->title === 'New Chat') {
                $title = $this->generateTitleFromMessage($firstUserMessage->content);
                $project->update(['title' => $title]);
            }
        }
    }

    private function generateTitleFromMessage($message)
    {
        // Simple title generation - take first few words
        $words = explode(' ', $message);
        $title = implode(' ', array_slice($words, 0, 5));
        return strlen($title) > 30 ? substr($title, 0, 30) . '...' : $title;
    }
}
