<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Listar todos os planos ativos
     */
    public function index(): JsonResponse
    {
        $plans = Plan::active()
            ->orderBy('price')
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'description' => $plan->description,
                    'price' => $plan->price,
                    'billing_cycle' => $plan->billing_cycle,
                    'features' => $plan->features,
                    'max_slides' => $plan->max_slides,
                    'max_presentations' => $plan->max_presentations,
                    'is_free' => $plan->isFree(),
                ];
            });

        return response()->json([
            'plans' => $plans,
        ]);
    }

    /**
     * Obter uso atual do plano do usuário
     */
    public function usage(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json($user->getPlanUsage());
    }

    /**
     * Fazer upgrade/downgrade de plano
     * Nota: Em produção, isso seria integrado com Stripe/Paddle
     */
    public function changePlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_slug' => ['required', 'string', 'exists:plans,slug'],
        ]);

        $user = $request->user();
        $newPlan = Plan::where('slug', $validated['plan_slug'])->first();

        if (!$newPlan->is_active) {
            return response()->json([
                'message' => 'Este plano não está disponível.',
            ], 400);
        }

        $currentPlan = $user->plan;
        $isUpgrade = !$currentPlan || $newPlan->price > ($currentPlan->price ?? 0);

        // Em produção, aqui seria a integração com gateway de pagamento
        // Por enquanto, apenas mudamos o plano diretamente

        $user->update([
            'plan_id' => $newPlan->id,
            'plan_expires_at' => $newPlan->isFree() ? null : now()->addMonth(),
        ]);

        return response()->json([
            'message' => $isUpgrade ? 'Upgrade realizado com sucesso!' : 'Plano alterado com sucesso!',
            'plan' => [
                'name' => $newPlan->name,
                'slug' => $newPlan->slug,
                'features' => $newPlan->features,
            ],
            'is_upgrade' => $isUpgrade,
            // Em produção, retornaria URL de checkout se for upgrade pago
            // 'checkout_url' => $checkoutUrl,
        ]);
    }

    /**
     * Webhook para processar pagamentos (Stripe/Paddle)
     * Nota: Implementação placeholder para integração futura
     */
    public function webhook(Request $request): JsonResponse
    {
        // Aqui seria implementada a lógica para processar webhooks do gateway
        // Por exemplo: payment_intent.succeeded, subscription.updated, etc.

        $payload = $request->all();
        $event = $payload['type'] ?? null;

        switch ($event) {
            case 'checkout.session.completed':
                // Ativar plano após pagamento
                break;
            case 'invoice.paid':
                // Renovar plano
                break;
            case 'customer.subscription.deleted':
                // Cancelar plano
                break;
        }

        return response()->json(['received' => true]);
    }
}

