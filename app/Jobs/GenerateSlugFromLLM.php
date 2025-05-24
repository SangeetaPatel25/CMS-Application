<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use OpenAI\Laravel\Facades\OpenAI;

class GenerateSlugFromLLM implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'Create a URL-friendly slug based on this article. Return just the slug.'],
                ['role' => 'user', 'content' => "{$this->article->title}\n\n{$this->article->content}"],
            ],
        ]);

        $slug = Str::slug($response['choices'][0]['message']['content']);
        $uniqueSlug = $this->makeUniqueSlug($slug);

        $this->article->update(['slug' => $uniqueSlug]);
    }
    private function makeUniqueSlug($slug)
    {
        $original = $slug;
        $i = 1;
        while (Article::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }
}
