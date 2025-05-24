<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Jobs\GenerateSlugJob;
use App\Jobs\GenerateSummaryJob;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->role === 'admin') {
            return Article::with('categories', 'author')->paginate(10);
        }
        return Article::with('categories', 'author')->where('author_id', $user->id)->paginate(10);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_ids' => 'array|exists:categories,id',
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'published_at' => 'nullable|date',
        ]);

        $article = new Article($data);
        $article->author_id = $user->id;
        $article->save();

        if (!empty($data['category_ids'])) {
            $article->categories()->sync($data['category_ids']);
        }

        // Dispatch async jobs for slug & summary generation
        GenerateSlugJob::dispatch($article->id);
        GenerateSummaryJob::dispatch($article->id);

        return response($article->load('categories', 'author'), 201);
    }

    public function show(Article $article, Request $request)
    {
        $user = $request->user();
        if ($user->role === 'author' && $article->author_id !== $user->id) {
            return response(['message' => 'Unauthorized'], 403);
        }
        return $article->load('categories', 'author');
    }

     public function update(Request $request, Article $article)
    {
        $user = $request->user();

        if ($user->role === 'author' && $article->author_id !== $user->id) {
            return response(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'title' => 'string|max:255',
            'content' => 'string',
            'category_ids' => 'array|exists:categories,id',
            'status' => Rule::in(['draft', 'published', 'archived']),
            'published_at' => 'nullable|date',
        ]);

        $article->update($data);

        if (isset($data['category_ids'])) {
            $article->categories()->sync($data['category_ids']);
        }

        if (isset($data['title']) || isset($data['content'])) {
            GenerateSlugJob::dispatch($article->id);
            GenerateSummaryJob::dispatch($article->id);
        }

        return $article->load('categories', 'author');
    }

    public function destroy(Article $article, Request $request)
    {
        $user = $request->user();
        if ($user->role === 'author' && $article->author_id !== $user->id) {
            return response(['message' => 'Unauthorized'], 403);
        }

        $article->delete();
        return response(null, 204);
    }

}
