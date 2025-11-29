<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SocialAuthController extends Controller
{
    private string $frontendCallback = 'https://app.azendeme.com.br/auth/callback';

    /**
     * URL de redirecionamento Google OAuth
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
     * Callback Google OAuth
     */
    public function googleCallback(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        try {
            // Trocar code por token
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => config('services.google.redirect'),
                'grant_type' => 'authorization_code',
                'code' => $validated['code'],
            ]);

            if (!$tokenResponse->successful()) {
                return $this->redirectWithError("Erro ao autenticar com Google.");
            }

            $accessToken = $tokenResponse->json('access_token');

            // Obter dados do usuário
            $userResponse = Http::withToken($accessToken)
                ->get('https://www.googleapis.com/oauth2/v2/userinfo');

            if (!$userResponse->successful()) {
                return $this->redirectWithError("Erro ao obter dados da conta Google.");
            }

            $googleUser = $userResponse->json();

            // Criar ou atualizar usuário
            $user = $this->findOrCreateUser($googleUser, 'google');

            // Criar token de API
            $token = $user->createToken('auth_token')->plainTextToken;

            // 🔥 Redirecionar para o frontend
            return redirect()->away($this->frontendCallback . '?token=' . $token);

        } catch (\Exception $e) {
            return $this->redirectWithError($e->getMessage());
        }
    }

    /**
     * URL de redirecionamento GitHub OAuth
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
     * Callback GitHub OAuth
     */
    public function githubCallback(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        try {
            // Trocar code por token
            $tokenResponse = Http::asForm()
                ->withHeaders(['Accept' => 'application/json'])
                ->post('https://github.com/login/oauth/access_token', [
                    'client_id' => config('services.github.client_id'),
                    'client_secret' => config('services.github.client_secret'),
                    'code' => $validated['code'],
                    'redirect_uri' => config('services.github.redirect'),
                ]);

            if (!$tokenResponse->successful()) {
                return $this->redirectWithError("Erro ao autenticar com GitHub.");
            }

            $accessToken = $tokenResponse->json('access_token');

            if (!$accessToken) {
                return $this->redirectWithError("Token de acesso não recebido do GitHub.");
            }

            // Obter dados do usuário
            $userResponse = Http::withToken($accessToken)
                ->withHeaders(['Accept' => 'application/json'])
                ->get('https://api.github.com/user');

            if (!$userResponse->successful()) {
                return $this->redirectWithError("Erro ao obter dados do GitHub.");
            }

            $githubUser = $userResponse->json();

            // Buscar email se não estiver vindo
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
                return $this->redirectWithError("Não foi possível obter email da conta GitHub.");
            }

            // Criar ou atualizar usuário
            $user = $this->findOrCreateUser([
                'id' => $githubUser['id'],
                'name' => $githubUser['name'] ?? $githubUser['login'],
                'email' => $githubUser['email'],
                'picture' => $githubUser['avatar_url'],
            ], 'github');

            // Criar token
            $token = $user->createToken('auth_token')->plainTextToken;

            // 🔥 Redirecionar para frontend
            return redirect()->away($this->frontendCallback . '?token=' . $token);

        } catch (\Exception $e) {
            return $this->redirectWithError($e->getMessage());
        }
    }

    /**
     * Criar ou atualizar usuário
     */
    private function findOrCreateUser(array $providerUser, string $provider): User
    {
        $user = User::where('provider', $provider)
            ->where('provider_id', $providerUser['id'])
            ->first();

        if ($user) {
            $avatar = $providerUser['picture'] ?? $providerUser['avatar_url'] ?? null;
            if ($avatar && $user->avatar !== $avatar) {
                $user->update(['avatar' => $avatar]);
            }
            return $user;
        }

        // Verificar usuário por email
        $existingUser = User::where('email', $providerUser['email'])->first();

        if ($existingUser) {
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
            'email_verified_at' => now(),
            'role' => 'user',
            'plan_id' => $freePlan?->id,
            'password' => null,
        ]);
    }

    /**
     * Redireciona para o front com erro
     */
    private function redirectWithError(string $message)
    {
        return redirect()->away($this->frontendCallback . '?error=' . urlencode($message));
    }
}
