<?php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'TikTok Clone API']);
});

Route::get('/api/categories', function () {
    return response()->json([
        ['id' => 1, 'name' => 'Semua', 'slug' => 'semua'],
        ['id' => 2, 'name' => 'Komedi', 'slug' => 'komedi']
    ]);
});