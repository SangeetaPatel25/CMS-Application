<?php

// app/Jobs/GenerateSlugJob.php

namespace App\Jobs;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class GenerateSlugJob implements ShouldQueue
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

        $prompt = "Generate a short, SEO-friendly slug for an article titled: '{$article->title}'. Avoid stop words and ensure it's URL-safe.";

        $response = Http::withToken(config('services.openai.api_key'))
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model'),
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an assistant that creates slugs.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 20,
            ]);

        $slug = Str::slug(trim($response['choices'][0]['message']['content']));

        // Ensure uniqueness
        $originalSlug = $slug;
        $i = 1;
        while (Article::where('slug', $slug)->where('id', '!=', $article->id)->exists()) {
            $slug = $originalSlug . '-' . $i++;
        }

        $article->slug = $slug;
        $article->save();
    }
}
