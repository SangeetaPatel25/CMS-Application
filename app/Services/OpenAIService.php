<?php

namespace App\Services;

use OpenAI;

class OpenAIService
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::client(config('services.openai.key'));
    }

    public function generateSlug(string $title, string $content): string
    {
        $prompt = "Generate a short, URL-friendly slug based on this title and content.\nTitle: $title\nContent: $content\nSlug:";

        $response = $this->client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => 10,
        ]);

        return trim($response->choices[0]->message->content);
    }

    public function generateSummary(string $content): string
    {
        $prompt = "Write a brief 2-3 sentence summary of the following article content:\n\n$content\n\nSummary:";

        $response = $this->client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => 60,
        ]);

        return trim($response->choices[0]->message->content);
    }
}
