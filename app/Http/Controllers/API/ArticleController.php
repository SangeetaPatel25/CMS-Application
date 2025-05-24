<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\GenerateSlugFromLLM;
use App\Jobs\GenerateSummaryFromLLM;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ArticleController extends Controller
{
    use AuthorizesRequests;
    // List articles
    public function index()
    {
        $articles = Article::with('author')->get();
        return response()->json($articles);
    }


    public function list(Request $request)
    {
        $query = Article::with('categories', 'author');

        if ($category = $request->query('category')) {
            $query->whereHas('categories', fn($q) => $q->where('name', $category));
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($start = $request->query('start_date')) {
            $query->whereDate('published_at', '>=', $start);
        }
        if ($end = $request->query('end_date')) {
            $query->whereDate('published_at', '<=', $end);
        }

        return $query->paginate(10);
    }

    // Create article
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'content'      => 'required|string',
            'categories'   => 'array',
            'status'       => 'in:draft,published,archived',
            'published_at' => 'nullable|date',
        ]);

        $data['author_id'] = Auth::id();

        // Start with a slug based on the title
        $slug = \Str::slug($data['title']);
        $originalSlug = $slug;
        $count = 1;

        // Check if slug already exists, append count if so
        while (Article::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $data['slug'] = $slug;

        // Temporarily set summary to null
        $data['summary'] = null;

        $article = Article::create($data);

        // Sync categories (if set)
        if (!empty($data['categories'])) {
            $article->categories()->sync($data['categories']);
        }

        // Dispatch LLM-powered async jobs
        GenerateSlugFromLLM::dispatch($article);
        GenerateSummaryFromLLM::dispatch($article);

        return response()->json([
            'message' => 'Article created. Slug and summary will be generated shortly.',
            'article' => $article,
        ], 201);
    }

    // Show single article
    public function show(Article $article)
    {
        return response()->json($article);
    }

    // Update article
    public function update(Request $request, Article $article)
    {
        $data = $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'content'     => 'sometimes|required|string',
            'categories'  => 'sometimes|array',
            'status'      => 'sometimes|in:draft,published,archived',
            'published_at' => 'nullable|date',
        ]);

        if (isset($data['title'])) {
            $data['slug'] = \Str::slug($data['title']);
        }

        $article->update($data);

        return response()->json($article);
    }

    // Delete article
    public function destroy(Article $article)
    {
        $article->delete();

        return response()->json(null, 204);
    }
}
