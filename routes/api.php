<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ArticleController;
use App\Http\Controllers\CategoryController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('articles', ArticleController::class);
});
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('categories', CategoryController::class);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('articles', ArticleController::class);

    // List/filter with query params: category, status, date range
    Route::get('articles-list', [ArticleController::class, 'list']);
});
// Fallback route for undefined API endpoints
Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found.'
    ], 404);
});
