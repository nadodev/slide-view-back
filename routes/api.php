<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DraftController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\PresentationController;
use App\Http\Controllers\Api\ShareController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\TemplateController;
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
    
    // Login Social
    Route::get('/providers', [SocialAuthController::class, 'providers']);
    
    // Google OAuth
    Route::get('/google/redirect', [SocialAuthController::class, 'googleRedirect']);
    Route::get('/google/callback', [SocialAuthController::class, 'googleCallback']);
    
    // GitHub OAuth
    Route::get('/github/redirect', [SocialAuthController::class, 'githubRedirect']);
    Route::get('/github/callback', [SocialAuthController::class, 'githubCallback']);
    
    // Rota de teste para debug
    Route::get('/test-callback', function (Request $request) {
        \Log::info('Test Callback - Rota funcionando', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'query' => $request->query(),
        ]);
        return response()->json([
            'message' => 'Rota de teste funcionando',
            'method' => $request->method(),
            'query' => $request->query(),
        ]);
    });
});

// Rotas protegidas (requerem autenticação via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    
    // Rotas de autenticação
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Rotas de planos
    Route::prefix('plans')->group(function () {
        Route::get('/usage', [PlanController::class, 'usage']);
        Route::post('/change', [PlanController::class, 'changePlan']);
    });

    // Rotas de assinatura (Asaas)
    Route::prefix('subscription')->group(function () {
        Route::get('/status', [SubscriptionController::class, 'status']);
        Route::post('/checkout', [SubscriptionController::class, 'checkout']);
        Route::post('/cancel', [SubscriptionController::class, 'cancel']);
        Route::get('/payments', [SubscriptionController::class, 'payments']);
        Route::get('/payments/{payment}', [SubscriptionController::class, 'paymentDetails']);
    });

    // Rotas de rascunhos (auto-save)
    Route::prefix('drafts')->group(function () {
        Route::get('/', [DraftController::class, 'index']);
        Route::post('/', [DraftController::class, 'save']);
        Route::get('/{draft}', [DraftController::class, 'show']);
        Route::delete('/{draft}', [DraftController::class, 'destroy']);
        Route::post('/cleanup', [DraftController::class, 'cleanup']);
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
        
        // Versões de slides
        Route::get('/{presentation}/slides/{slide}/versions', [PresentationController::class, 'getSlideVersions']);
        Route::post('/{presentation}/slides/{slide}/versions', [PresentationController::class, 'saveSlideVersion']);
        Route::post('/{presentation}/slides/{slide}/versions/{version}/restore', [PresentationController::class, 'restoreSlideVersion']);
        
        // Ações especiais
        Route::post('/{presentation}/duplicate', [PresentationController::class, 'duplicate']);

        // Compartilhamento
        Route::get('/{presentation}/share', [ShareController::class, 'getShareSettings']);
        Route::post('/{presentation}/share/enable', [ShareController::class, 'enableSharing']);
        Route::post('/{presentation}/share/disable', [ShareController::class, 'disableSharing']);
        Route::put('/{presentation}/share', [ShareController::class, 'updateShareSettings']);
        Route::post('/{presentation}/share/regenerate', [ShareController::class, 'regenerateToken']);
    });

    // Templates
    Route::prefix('templates')->group(function () {
        Route::get('/', [TemplateController::class, 'index']);
        Route::get('/categories', [TemplateController::class, 'categories']);
        Route::get('/{template}', [TemplateController::class, 'show']);
        Route::post('/{template}/use', [TemplateController::class, 'useTemplate']);
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
Route::get('/plans', [PlanController::class, 'index']);

// Rotas públicas para visualização de apresentações compartilhadas
Route::prefix('public')->group(function () {
    Route::get('/presentations/{token}', [ShareController::class, 'viewPublic']);
    Route::get('/embed/{token}', [ShareController::class, 'getEmbed']);
});

// Templates públicos (listagem)
Route::get('/templates/public', [TemplateController::class, 'index']);
Route::get('/templates/categories', [TemplateController::class, 'categories']);

// Webhooks de pagamento - sem autenticação
Route::post('/webhooks/payments', [PlanController::class, 'webhook']); // Legacy
Route::post('/webhooks/asaas', [SubscriptionController::class, 'webhook']); // Asaas

