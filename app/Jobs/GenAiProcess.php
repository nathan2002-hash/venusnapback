<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\GenAi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenAiProcess implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $genaiId,
        public string $description,
        public int $userId
    ) {}

    /**
     * Execute the job.
     */
   public function handle()
    {
        $genai = GenAi::findOrFail($this->genaiId);
        $user = User::findOrFail($this->userId);
    
        try {
            $genai->update(['status' => 'processing']);
    
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.openai.com/v1/images/generations', [
                'model' => 'dall-e-2', // or 'dall-e-3' if you want DALL-E 3
                'prompt' => $this->description,
                'n' => 1,
                'size' => '1024x1024', // Standard size
                'response_format' => 'url' // we want a URL first
            ]);
    
            if ($response->successful()) {
                $imageUrl = $response->json('data.0.url');
    
                // Download the image from the URL and save it to your storage
                $imageContents = Http::get($imageUrl)->body();
    
                $fileName = 'genai/' . uniqid() . '.jpeg';
                Storage::put($fileName, $imageContents);
    
                $genai->update([
                    'file_path' => $fileName,
                    'status' => 'completed',
                ]);
    
                // Decrement user points (adjust if needed)
                $user->decrement('points', 30); 
    
            } else {
                $genai->update(['status' => 'failed']);
                Log::error('DALL-E image generation failed: ' . $response->body());
            }
    
        } catch (\Exception $e) {
            $genai->update(['status' => 'failed']);
            Log::error("Ad generation failed: " . $e->getMessage());
        }
    }
}
