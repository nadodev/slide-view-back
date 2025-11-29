<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Laravel Backend funcionando!',
        'timestamp' => now()->toIso8601String(),
    ]);
});
