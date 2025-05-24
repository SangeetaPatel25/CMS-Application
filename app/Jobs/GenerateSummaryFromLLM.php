<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateSummaryFromLLM implements ShouldQueue
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
                ['role' => 'system', 'content' => 'Summarize the following article in 2-3 concise sentences.'],
                ['role' => 'user', 'content' => $this->article->content],
            ],
        ]);

        $summary = trim($response['choices'][0]['message']['content']);
        $this->article->update(['summary' => $summary]);
    }
}
