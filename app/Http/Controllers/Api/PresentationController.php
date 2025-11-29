<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presentation;
use App\Models\Slide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresentationController extends Controller
{
    /**
     * Listar todas as apresentações do usuário
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $presentations = $user->presentations()
            ->recent()
            ->withCount('slides')
            ->get();

        return response()->json([
            'presentations' => $presentations,
        ]);
    }

    /**
     * Criar nova apresentação
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Verificar limite do plano
        if (!$user->canCreatePresentation()) {
            return response()->json([
                'message' => 'Você atingiu o limite de apresentações do seu plano. Faça upgrade para criar mais.',
            ], 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'settings' => ['nullable', 'array'],
            'slides' => ['nullable', 'array'],
            'slides.*.title' => ['nullable', 'string', 'max:255'],
            'slides.*.content' => ['required', 'string'],
            'slides.*.notes' => ['nullable', 'string'],
            'slides.*.metadata' => ['nullable', 'array'],
        ]);

        try {
            DB::beginTransaction();

            $presentation = $user->presentations()->create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'settings' => $validated['settings'] ?? null,
                'status' => 'draft',
                'last_edited_at' => now(),
            ]);

            // Criar slides se fornecidos
            if (!empty($validated['slides'])) {
                foreach ($validated['slides'] as $index => $slideData) {
                    $presentation->slides()->create([
                        'order' => $index,
                        'title' => $slideData['title'] ?? null,
                        'content' => $slideData['content'],
                        'notes' => $slideData['notes'] ?? null,
                        'metadata' => $slideData['metadata'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Apresentação criada com sucesso!',
                'presentation' => $presentation->load('slides'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao criar apresentação.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exibir uma apresentação específica
     */
    public function show(Request $request, Presentation $presentation): JsonResponse
    {
        // Verificar se a apresentação pertence ao usuário
        if ($presentation->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Apresentação não encontrada.',
            ], 404);
        }

        return response()->json([
            'presentation' => $presentation->load('slides'),
        ]);
    }

    /**
     * Atualizar apresentação
     */
    public function update(Request $request, Presentation $presentation): JsonResponse
    {
        // Verificar se a apresentação pertence ao usuário
        if ($presentation->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Apresentação não encontrada.',
            ], 404);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'thumbnail' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:draft,published,archived'],
            'settings' => ['nullable', 'array'],
        ]);

        $presentation->update([
            ...$validated,
            'last_edited_at' => now(),
        ]);

        return response()->json([
            'message' => 'Apresentação atualizada com sucesso!',
            'presentation' => $presentation->load('slides'),
        ]);
    }

    /**
     * Deletar apresentação
     */
    public function destroy(Request $request, Presentation $presentation): JsonResponse
    {
        // Verificar se a apresentação pertence ao usuário
        if ($presentation->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Apresentação não encontrada.',
            ], 404);
        }

        $presentation->delete();

        return response()->json([
            'message' => 'Apresentação deletada com sucesso!',
        ]);
    }

    /**
     * Atualizar slides da apresentação (substitui todos os slides)
     */
    public function updateSlides(Request $request, Presentation $presentation): JsonResponse
    {
        // Verificar se a apresentação pertence ao usuário
        if ($presentation->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Apresentação não encontrada.',
            ], 404);
        }

        $validated = $request->validate([
            'slides' => ['required', 'array'],
            'slides.*.id' => ['nullable', 'integer'],
            'slides.*.title' => ['nullable', 'string', 'max:255'],
            'slides.*.content' => ['required', 'string'],
            'slides.*.notes' => ['nullable', 'string'],
            'slides.*.metadata' => ['nullable', 'array'],
        ]);

        try {
            DB::beginTransaction();

            // Deletar slides antigos
            $presentation->slides()->delete();

            // Criar novos slides
            foreach ($validated['slides'] as $index => $slideData) {
                $presentation->slides()->create([
                    'order' => $index,
                    'title' => $slideData['title'] ?? null,
                    'content' => $slideData['content'],
                    'notes' => $slideData['notes'] ?? null,
                    'metadata' => $slideData['metadata'] ?? null,
                ]);
            }

            $presentation->update(['last_edited_at' => now()]);

            DB::commit();

            return response()->json([
                'message' => 'Slides atualizados com sucesso!',
                'presentation' => $presentation->fresh()->load('slides'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao atualizar slides.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Adicionar um slide à apresentação
     */
    public function addSlide(Request $request, Presentation $presentation): JsonResponse
    {
        // Verificar se a apresentação pertence ao usuário
        if ($presentation->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Apresentação não encontrada.',
            ], 404);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'order' => ['nullable', 'integer'],
        ]);

        $order = $validated['order'] ?? $presentation->slides()->count();

        $slide = $presentation->slides()->create([
            'order' => $order,
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
            'notes' => $validated['notes'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
        ]);

        $presentation->update(['last_edited_at' => now()]);

        return response()->json([
            'message' => 'Slide adicionado com sucesso!',
            'slide' => $slide,
        ], 201);
    }

    /**
     * Atualizar um slide específico
     */
    public function updateSlide(Request $request, Presentation $presentation, Slide $slide): JsonResponse
    {
        // Verificar se a apresentação pertence ao usuário
        if ($presentation->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Apresentação não encontrada.',
            ], 404);
        }

        // Verificar se o slide pertence à apresentação
        if ($slide->presentation_id !== $presentation->id) {
            return response()->json([
                'message' => 'Slide não encontrado.',
            ], 404);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'notes' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
            'order' => ['nullable', 'integer'],
        ]);

        $slide->update($validated);
        $presentation->update(['last_edited_at' => now()]);

        return response()->json([
            'message' => 'Slide atualizado com sucesso!',
            'slide' => $slide,
        ]);
    }

    /**
     * Deletar um slide específico
     */
    public function deleteSlide(Request $request, Presentation $presentation, Slide $slide): JsonResponse
    {
        // Verificar se a apresentação pertence ao usuário
        if ($presentation->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Apresentação não encontrada.',
            ], 404);
        }

        // Verificar se o slide pertence à apresentação
        if ($slide->presentation_id !== $presentation->id) {
            return response()->json([
                'message' => 'Slide não encontrado.',
            ], 404);
        }

        $slide->delete();
        $presentation->update(['last_edited_at' => now()]);

        return response()->json([
            'message' => 'Slide deletado com sucesso!',
        ]);
    }

    /**
     * Duplicar uma apresentação
     */
    public function duplicate(Request $request, Presentation $presentation): JsonResponse
    {
        $user = $request->user();

        // Verificar se a apresentação pertence ao usuário
        if ($presentation->user_id !== $user->id) {
            return response()->json([
                'message' => 'Apresentação não encontrada.',
            ], 404);
        }

        // Verificar limite do plano
        if (!$user->canCreatePresentation()) {
            return response()->json([
                'message' => 'Você atingiu o limite de apresentações do seu plano.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            $newPresentation = $presentation->replicate();
            $newPresentation->title = $presentation->title . ' (Cópia)';
            $newPresentation->status = 'draft';
            $newPresentation->last_edited_at = now();
            $newPresentation->save();

            foreach ($presentation->slides as $slide) {
                $newSlide = $slide->replicate();
                $newSlide->presentation_id = $newPresentation->id;
                $newSlide->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Apresentação duplicada com sucesso!',
                'presentation' => $newPresentation->load('slides'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao duplicar apresentação.',
            ], 500);
        }
    }
}

