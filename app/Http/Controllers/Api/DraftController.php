<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Draft;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DraftController extends Controller
{
    /**
     * Listar rascunhos do usuário
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $drafts = $user->drafts()
            ->recent()
            ->limit(10)
            ->get();

        return response()->json([
            'drafts' => $drafts,
        ]);
    }

    /**
     * Salvar/atualizar rascunho (auto-save)
     */
    public function save(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'presentation_id' => ['nullable', 'integer', 'exists:presentations,id'],
            'type' => ['required', 'in:presentation,slide'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'metadata' => ['nullable', 'array'],
        ]);

        // Verificar se já existe um rascunho para esta apresentação/tipo
        $draft = Draft::where('user_id', $user->id)
            ->where('presentation_id', $validated['presentation_id'])
            ->where('type', $validated['type'])
            ->first();

        if ($draft) {
            $draft->update([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'metadata' => $validated['metadata'] ?? $draft->metadata,
                'last_saved_at' => now(),
            ]);
        } else {
            $draft = Draft::create([
                'user_id' => $user->id,
                'presentation_id' => $validated['presentation_id'],
                'type' => $validated['type'],
                'title' => $validated['title'],
                'content' => $validated['content'],
                'metadata' => $validated['metadata'],
                'last_saved_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Rascunho salvo automaticamente',
            'draft' => $draft,
            'saved_at' => $draft->last_saved_at->toISOString(),
        ]);
    }

    /**
     * Recuperar rascunho específico
     */
    public function show(Request $request, Draft $draft): JsonResponse
    {
        if ($draft->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Rascunho não encontrado.'], 404);
        }

        return response()->json([
            'draft' => $draft,
        ]);
    }

    /**
     * Deletar rascunho
     */
    public function destroy(Request $request, Draft $draft): JsonResponse
    {
        if ($draft->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Rascunho não encontrado.'], 404);
        }

        $draft->delete();

        return response()->json([
            'message' => 'Rascunho removido.',
        ]);
    }

    /**
     * Limpar rascunhos antigos (mais de 7 dias)
     */
    public function cleanup(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $deleted = Draft::where('user_id', $user->id)
            ->where('last_saved_at', '<', now()->subDays(7))
            ->delete();

        return response()->json([
            'message' => "Removidos {$deleted} rascunhos antigos.",
            'deleted_count' => $deleted,
        ]);
    }
}

