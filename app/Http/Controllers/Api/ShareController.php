<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presentation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    /**
     * Obter configurações de compartilhamento
     */
    public function getShareSettings(Request $request, Presentation $presentation): JsonResponse
    {
        if ($presentation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Apresentação não encontrada.'], 404);
        }

        $baseUrl = config('app.frontend_url', 'http://localhost:5173');

        return response()->json([
            'is_public' => $presentation->is_public,
            'allow_embed' => $presentation->allow_embed,
            'share_token' => $presentation->share_token,
            'shared_at' => $presentation->shared_at,
            'view_count' => $presentation->view_count,
            'share_url' => $presentation->share_token 
                ? "{$baseUrl}/view/{$presentation->share_token}" 
                : null,
            'embed_code' => $presentation->allow_embed && $presentation->share_token
                ? $this->generateEmbedCode($presentation, $baseUrl)
                : null,
        ]);
    }

    /**
     * Habilitar compartilhamento
     */
    public function enableSharing(Request $request, Presentation $presentation): JsonResponse
    {
        if ($presentation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Apresentação não encontrada.'], 404);
        }

        $validated = $request->validate([
            'allow_embed' => ['sometimes', 'boolean'],
        ]);

        $presentation->enablePublicSharing($validated['allow_embed'] ?? false);

        $baseUrl = config('app.frontend_url', 'http://localhost:5173');

        return response()->json([
            'message' => 'Compartilhamento habilitado!',
            'share_url' => "{$baseUrl}/view/{$presentation->share_token}",
            'embed_code' => $presentation->allow_embed
                ? $this->generateEmbedCode($presentation, $baseUrl)
                : null,
            'share_token' => $presentation->share_token,
        ]);
    }

    /**
     * Desabilitar compartilhamento
     */
    public function disableSharing(Request $request, Presentation $presentation): JsonResponse
    {
        if ($presentation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Apresentação não encontrada.'], 404);
        }

        $presentation->disablePublicSharing();

        return response()->json([
            'message' => 'Compartilhamento desabilitado!',
        ]);
    }

    /**
     * Atualizar configurações de compartilhamento
     */
    public function updateShareSettings(Request $request, Presentation $presentation): JsonResponse
    {
        if ($presentation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Apresentação não encontrada.'], 404);
        }

        $validated = $request->validate([
            'allow_embed' => ['sometimes', 'boolean'],
        ]);

        $presentation->update($validated);

        $baseUrl = config('app.frontend_url', 'http://localhost:5173');

        return response()->json([
            'message' => 'Configurações atualizadas!',
            'embed_code' => $presentation->allow_embed && $presentation->share_token
                ? $this->generateEmbedCode($presentation, $baseUrl)
                : null,
        ]);
    }

    /**
     * Regenerar token de compartilhamento
     */
    public function regenerateToken(Request $request, Presentation $presentation): JsonResponse
    {
        if ($presentation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Apresentação não encontrada.'], 404);
        }

        $token = $presentation->generateShareToken();
        $baseUrl = config('app.frontend_url', 'http://localhost:5173');

        return response()->json([
            'message' => 'Token regenerado!',
            'share_token' => $token,
            'share_url' => "{$baseUrl}/view/{$token}",
            'embed_code' => $presentation->allow_embed
                ? $this->generateEmbedCode($presentation, $baseUrl)
                : null,
        ]);
    }

    /**
     * Visualizar apresentação pública (sem autenticação)
     */
    public function viewPublic(string $token): JsonResponse
    {
        $presentation = Presentation::where('share_token', $token)
            ->where('is_public', true)
            ->with(['slides', 'user:id,name'])
            ->first();

        if (!$presentation) {
            return response()->json([
                'message' => 'Apresentação não encontrada ou não está mais disponível.',
            ], 404);
        }

        // Incrementar visualizações
        $presentation->incrementViewCount();

        return response()->json([
            'presentation' => [
                'id' => $presentation->id,
                'title' => $presentation->title,
                'description' => $presentation->description,
                'author' => $presentation->user->name,
                'slide_count' => $presentation->slide_count,
                'slides' => $presentation->slides,
                'settings' => $presentation->settings,
                'view_count' => $presentation->view_count,
            ],
        ]);
    }

    /**
     * Obter dados para embed (sem autenticação)
     */
    public function getEmbed(string $token): JsonResponse
    {
        $presentation = Presentation::where('share_token', $token)
            ->where('is_public', true)
            ->where('allow_embed', true)
            ->with('slides')
            ->first();

        if (!$presentation) {
            return response()->json([
                'message' => 'Apresentação não encontrada ou embed não permitido.',
            ], 404);
        }

        $presentation->incrementViewCount();

        return response()->json([
            'title' => $presentation->title,
            'slides' => $presentation->slides,
            'settings' => $presentation->settings,
            'slide_count' => $presentation->slide_count,
        ]);
    }

    /**
     * Gerar código de embed
     */
    private function generateEmbedCode(Presentation $presentation, string $baseUrl): string
    {
        $embedUrl = "{$baseUrl}/embed/{$presentation->share_token}";
        $title = htmlspecialchars($presentation->title);
        
        return <<<HTML
<iframe 
    src="{$embedUrl}" 
    width="100%" 
    height="500" 
    frameborder="0" 
    allowfullscreen
    title="{$title}"
    style="border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
</iframe>
HTML;
    }
}

