<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$plans  Slugs dos planos permitidos
     */
    public function handle(Request $request, Closure $next, string ...$plans): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Não autenticado.',
            ], 401);
        }

        // Se não tem plano, verificar se 'free' está permitido
        if (!$user->plan) {
            if (in_array('free', $plans)) {
                return $next($request);
            }

            return response()->json([
                'message' => 'Você precisa de um plano para acessar este recurso.',
            ], 403);
        }

        // Verificar se o plano do usuário está na lista de planos permitidos
        if (!in_array($user->plan->slug, $plans)) {
            return response()->json([
                'message' => 'Seu plano não permite acesso a este recurso. Faça upgrade para continuar.',
                'current_plan' => $user->plan->slug,
                'required_plans' => $plans,
            ], 403);
        }

        // Verificar se o plano expirou
        if (!$user->hasPlanActive()) {
            return response()->json([
                'message' => 'Seu plano expirou. Renove para continuar.',
            ], 403);
        }

        return $next($request);
    }
}

