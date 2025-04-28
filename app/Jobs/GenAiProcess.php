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
                'Authorization' => 'Bearer '.env('STABLE_DIFFUSION_API_KEY'),
                'Accept' => 'image/*'
            ])
            ->asMultipart()
            ->post('https://api.stability.ai/v2beta/stable-image/generate/sd3', [
                ['name' => 'prompt', 'contents' => $this->description],
                ['name' => 'output_format', 'contents' => 'jpeg'],
                ['name' => 'none', 'contents' => '', 'filename' => 'none']
            ]);

            if ($response->successful()) {
                $fileName = 'genai/'.uniqid().'.jpeg';
                Storage::put($fileName, $response->body());

                $genai->update([
                    'file_path' => $fileName,
                    'status' => 'completed',
                ]);

                $user->decrement('points', 30);
            } else {
                $genai->update(['status' => 'failed']);
            }

        } catch (\Exception $e) {
            $genai->update(['status' => 'failed']);
            Log::error("Ad generation failed: ".$e->getMessage());
        }
    }
}
