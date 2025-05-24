<?php

namespace App\Jobs;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateSummaryJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $articleId;

    public function __construct($articleId)
    {
        $this->articleId = $articleId;
    }

    public function handle()
    {
        $article = Article::find($this->articleId);
        if (!$article) return;

        $prompt = "Summarize the following article content in 2-3 concise sentences:\n\n{$article->content}";

        $response = Http::withToken(config('services.openai.api_key'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model'),
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional content summarizer.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 150,
            ]);

        $summary = trim($response['choices'][0]['message']['content']);

        $article->summary = $summary;
        $article->save();
    }
}
