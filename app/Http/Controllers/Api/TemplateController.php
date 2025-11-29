<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Presentation;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TemplateController extends Controller
{
    /**
     * Listar todos os templates disponíveis
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $hasPremium = $user?->hasPlanActive() && $user?->plan?->slug !== 'free';

        $query = Template::active();

        // Filtrar por categoria se especificado
        if ($request->has('category')) {
            $query->category($request->category);
        }

        // Se não tem premium, mostrar apenas gratuitos
        if (!$hasPremium) {
            $templates = $query->get()->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'slug' => $template->slug,
                    'description' => $template->description,
                    'category' => $template->category,
                    'thumbnail' => $template->thumbnail,
                    'icon' => $template->icon,
                    'is_premium' => $template->is_premium,
                    'slide_count' => count($template->slides),
                    'usage_count' => $template->usage_count,
                    'locked' => $template->is_premium, // Premium está bloqueado
                ];
            });
        } else {
            $templates = $query->get()->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'slug' => $template->slug,
                    'description' => $template->description,
                    'category' => $template->category,
                    'thumbnail' => $template->thumbnail,
                    'icon' => $template->icon,
                    'is_premium' => $template->is_premium,
                    'slide_count' => count($template->slides),
                    'usage_count' => $template->usage_count,
                    'locked' => false,
                ];
            });
        }

        // Agrupar por categoria
        $grouped = $templates->groupBy('category');

        return response()->json([
            'templates' => $templates,
            'categories' => $grouped,
            'total' => $templates->count(),
        ]);
    }

    /**
     * Obter detalhes de um template
     */
    public function show(Template $template): JsonResponse
    {
        if (!$template->is_active) {
            return response()->json(['message' => 'Template não encontrado.'], 404);
        }

        return response()->json([
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'slug' => $template->slug,
                'description' => $template->description,
                'category' => $template->category,
                'thumbnail' => $template->thumbnail,
                'icon' => $template->icon,
                'slides' => $template->slides,
                'settings' => $template->settings,
                'is_premium' => $template->is_premium,
                'usage_count' => $template->usage_count,
            ],
        ]);
    }

    /**
     * Usar template para criar nova apresentação
     */
    public function useTemplate(Request $request, Template $template): JsonResponse
    {
        $user = $request->user();

        // Verificar se o template é premium e o usuário tem acesso
        if ($template->is_premium) {
            $hasPremium = $user->hasPlanActive() && $user->plan?->slug !== 'free';
            if (!$hasPremium) {
                return response()->json([
                    'message' => 'Este template é exclusivo para usuários Premium.',
                    'upgrade_required' => true,
                ], 403);
            }
        }

        // Verificar limite de apresentações
        if (!$user->canCreatePresentation()) {
            return response()->json([
                'message' => 'Você atingiu o limite de apresentações do seu plano.',
            ], 403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
        ]);

        try {
            DB::beginTransaction();

            // Criar apresentação a partir do template
            $presentation = Presentation::create([
                'user_id' => $user->id,
                'title' => $validated['title'] ?? $template->name,
                'description' => $template->description,
                'status' => 'draft',
                'settings' => $template->settings,
                'slide_count' => count($template->slides),
                'last_edited_at' => now(),
            ]);

            // Criar slides do template
            foreach ($template->slides as $index => $slideData) {
                $presentation->slides()->create([
                    'order' => $index,
                    'title' => $slideData['title'] ?? null,
                    'content' => $slideData['content'],
                    'notes' => $slideData['notes'] ?? null,
                    'metadata' => $slideData['metadata'] ?? null,
                ]);
            }

            // Incrementar uso do template
            $template->incrementUsage();

            DB::commit();

            return response()->json([
                'message' => 'Apresentação criada a partir do template!',
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
     * Listar categorias disponíveis
     */
    public function categories(): JsonResponse
    {
        $categories = [
            [
                'slug' => 'pitch',
                'name' => 'Pitch Deck',
                'description' => 'Apresentações para investidores e startups',
                'icon' => 'rocket',
            ],
            [
                'slug' => 'education',
                'name' => 'Aula / Workshop',
                'description' => 'Templates para ensino e treinamentos',
                'icon' => 'graduation-cap',
            ],
            [
                'slug' => 'report',
                'name' => 'Relatório Executivo',
                'description' => 'Relatórios de negócios e análises',
                'icon' => 'bar-chart',
            ],
            [
                'slug' => 'portfolio',
                'name' => 'Portfolio',
                'description' => 'Mostre seus trabalhos e projetos',
                'icon' => 'briefcase',
            ],
            [
                'slug' => 'proposal',
                'name' => 'Proposta Comercial',
                'description' => 'Propostas para clientes e parceiros',
                'icon' => 'file-text',
            ],
        ];

        return response()->json(['categories' => $categories]);
    }
}

