<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PresentationController;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rotas da API do SlideView
|
*/

// Rotas públicas (não requerem autenticação)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Rotas protegidas (requerem autenticação via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    
    // Rotas de autenticação
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Rotas de apresentações
    Route::prefix('presentations')->group(function () {
        Route::get('/', [PresentationController::class, 'index']);
        Route::post('/', [PresentationController::class, 'store']);
        Route::get('/{presentation}', [PresentationController::class, 'show']);
        Route::put('/{presentation}', [PresentationController::class, 'update']);
        Route::delete('/{presentation}', [PresentationController::class, 'destroy']);
        
        // Slides
        Route::put('/{presentation}/slides', [PresentationController::class, 'updateSlides']);
        Route::post('/{presentation}/slides', [PresentationController::class, 'addSlide']);
        Route::put('/{presentation}/slides/{slide}', [PresentationController::class, 'updateSlide']);
        Route::delete('/{presentation}/slides/{slide}', [PresentationController::class, 'deleteSlide']);
        
        // Ações especiais
        Route::post('/{presentation}/duplicate', [PresentationController::class, 'duplicate']);
    });
});

// Rotas apenas para administradores
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // Listar todos os planos (para gerenciamento)
    Route::get('/plans', function () {
        return response()->json([
            'plans' => Plan::all(),
        ]);
    });
});

// Rota para verificar se a API está funcionando
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API SlideView funcionando!',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Rota pública para listar planos disponíveis
Route::get('/plans', function () {
    return response()->json([
        'plans' => Plan::active()->get(['id', 'name', 'slug', 'description', 'price', 'billing_cycle', 'features']),
    ]);
});

