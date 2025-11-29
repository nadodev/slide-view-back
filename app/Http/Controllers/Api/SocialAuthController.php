<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    /**
     * Obter URL de redirecionamento para Google OAuth
     */
    public function googleRedirect(): JsonResponse
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect');
        
        if (!$clientId) {
            return response()->json([
                'message' => 'Login com Google não está configurado.',
            ], 503);
        }

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);

        return response()->json([
            'url' => 'https://accounts.google.com/o/oauth2/v2/auth?' . $params,
        ]);
    }

    /**
     * Callback do Google OAuth - troca code por token e cria/autentica usuário
     */
    public function googleCallback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        try {
            // Trocar código por access token
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => config('services.google.redirect'),
                'grant_type' => 'authorization_code',
                'code' => $validated['code'],
            ]);

            if (!$tokenResponse->successful()) {
                return response()->json([
                    'message' => 'Erro ao autenticar com Google.',
                    'error' => $tokenResponse->json(),
                ], 400);
            }

            $accessToken = $tokenResponse->json('access_token');

            // Obter dados do usuário
            $userResponse = Http::withToken($accessToken)
                ->get('https://www.googleapis.com/oauth2/v2/userinfo');

            if (!$userResponse->successful()) {
                return response()->json([
                    'message' => 'Erro ao obter dados do usuário.',
                ], 400);
            }

            $googleUser = $userResponse->json();

            // Criar ou atualizar usuário
            $user = $this->findOrCreateUser($googleUser, 'google');

            // Criar token de autenticação
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login realizado com sucesso!',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'role' => $user->role,
                    'plan' => $user->plan?->name ?? 'free',
                    'provider' => $user->provider,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao processar login com Google.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obter URL de redirecionamento para GitHub OAuth
     */
    public function githubRedirect(): JsonResponse
    {
        $clientId = config('services.github.client_id');
        $redirectUri = config('services.github.redirect');
        
        if (!$clientId) {
            return response()->json([
                'message' => 'Login com GitHub não está configurado.',
            ], 503);
        }

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => 'user:email',
        ]);

        return response()->json([
            'url' => 'https://github.com/login/oauth/authorize?' . $params,
        ]);
    }

    /**
     * Callback do GitHub OAuth
     */
    public function githubCallback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        try {
            // Trocar código por access token
            $tokenResponse = Http::asForm()
                ->withHeaders(['Accept' => 'application/json'])
                ->post('https://github.com/login/oauth/access_token', [
                    'client_id' => config('services.github.client_id'),
                    'client_secret' => config('services.github.client_secret'),
                    'code' => $validated['code'],
                    'redirect_uri' => config('services.github.redirect'),
                ]);

            if (!$tokenResponse->successful()) {
                return response()->json([
                    'message' => 'Erro ao autenticar com GitHub.',
                ], 400);
            }

            $accessToken = $tokenResponse->json('access_token');

            if (!$accessToken) {
                return response()->json([
                    'message' => 'Token de acesso não recebido.',
                    'error' => $tokenResponse->json(),
                ], 400);
            }

            // Obter dados do usuário
            $userResponse = Http::withToken($accessToken)
                ->withHeaders(['Accept' => 'application/json'])
                ->get('https://api.github.com/user');

            if (!$userResponse->successful()) {
                return response()->json([
                    'message' => 'Erro ao obter dados do usuário.',
                ], 400);
            }

            $githubUser = $userResponse->json();

            // Se o email não veio na resposta, buscar separadamente
            if (empty($githubUser['email'])) {
                $emailsResponse = Http::withToken($accessToken)
                    ->withHeaders(['Accept' => 'application/json'])
                    ->get('https://api.github.com/user/emails');

                if ($emailsResponse->successful()) {
                    $emails = $emailsResponse->json();
                    $primaryEmail = collect($emails)->firstWhere('primary', true);
                    $githubUser['email'] = $primaryEmail['email'] ?? $emails[0]['email'] ?? null;
                }
            }

            if (empty($githubUser['email'])) {
                return response()->json([
                    'message' => 'Não foi possível obter o email da conta GitHub. Verifique as permissões.',
                ], 400);
            }

            // Criar ou atualizar usuário
            $user = $this->findOrCreateUser([
                'id' => $githubUser['id'],
                'name' => $githubUser['name'] ?? $githubUser['login'],
                'email' => $githubUser['email'],
                'picture' => $githubUser['avatar_url'],
            ], 'github');

            // Criar token de autenticação
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login realizado com sucesso!',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'role' => $user->role,
                    'plan' => $user->plan?->name ?? 'free',
                    'provider' => $user->provider,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao processar login com GitHub.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Encontra ou cria um usuário baseado nos dados do provider
     */
    private function findOrCreateUser(array $providerUser, string $provider): User
    {
        // Primeiro, verificar se já existe usuário com esse provider_id
        $user = User::where('provider', $provider)
            ->where('provider_id', $providerUser['id'])
            ->first();

        if ($user) {
            // Atualizar avatar se mudou
            $avatar = $providerUser['picture'] ?? $providerUser['avatar_url'] ?? null;
            if ($avatar && $user->avatar !== $avatar) {
                $user->update(['avatar' => $avatar]);
            }
            return $user;
        }

        // Verificar se existe usuário com o mesmo email
        $existingUser = User::where('email', $providerUser['email'])->first();

        if ($existingUser) {
            // Vincular conta social ao usuário existente
            $existingUser->update([
                'provider' => $provider,
                'provider_id' => $providerUser['id'],
                'avatar' => $providerUser['picture'] ?? $providerUser['avatar_url'] ?? $existingUser->avatar,
            ]);
            return $existingUser;
        }

        // Criar novo usuário
        $freePlan = Plan::where('slug', 'free')->first();

        return User::create([
            'name' => $providerUser['name'],
            'email' => $providerUser['email'],
            'provider' => $provider,
            'provider_id' => $providerUser['id'],
            'avatar' => $providerUser['picture'] ?? $providerUser['avatar_url'] ?? null,
            'email_verified_at' => now(), // Email verificado pelo provider
            'role' => 'user',
            'plan_id' => $freePlan?->id,
            'password' => null, // Sem senha para login social
        ]);
    }

    /**
     * Verificar status dos providers configurados
     */
    public function providers(): JsonResponse
    {
        return response()->json([
            'providers' => [
                'google' => [
                    'enabled' => !empty(config('services.google.client_id')),
                ],
                'github' => [
                    'enabled' => !empty(config('services.github.client_id')),
                ],
            ],
        ]);
    }
}

