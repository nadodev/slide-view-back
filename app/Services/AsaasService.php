<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.asaas.sandbox')
            ? 'https://sandbox.asaas.com/api/v3'
            : 'https://api.asaas.com/v3';
        
        $this->apiKey = config('services.asaas.api_key');
    }

    /**
     * Fazer requisição para API do Asaas
     */
    protected function request(string $method, string $endpoint, array $data = [])
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->{$method}("{$this->baseUrl}{$endpoint}", $data);

        if (!$response->successful()) {
            Log::error('Asaas API Error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
            
            throw new \Exception($response->json('errors.0.description') ?? 'Erro na API do Asaas');
        }

        return $response->json();
    }

    /**
     * Criar ou atualizar cliente no Asaas
     */
    public function createOrUpdateCustomer(User $user): string
    {
        // Se já tem customer_id, atualiza
        if ($user->asaas_customer_id) {
            $this->request('put', "/customers/{$user->asaas_customer_id}", [
                'name' => $user->name,
                'email' => $user->email,
            ]);
            return $user->asaas_customer_id;
        }

        // Verifica se já existe cliente com esse email
        $existing = $this->request('get', '/customers', ['email' => $user->email]);
        
        if (!empty($existing['data'])) {
            $customerId = $existing['data'][0]['id'];
            $user->update(['asaas_customer_id' => $customerId]);
            return $customerId;
        }

        // Cria novo cliente
        $response = $this->request('post', '/customers', [
            'name' => $user->name,
            'email' => $user->email,
            'externalReference' => (string) $user->id,
        ]);

        $customerId = $response['id'];
        $user->update(['asaas_customer_id' => $customerId]);

        return $customerId;
    }

    /**
     * Criar assinatura
     */
    public function createSubscription(User $user, Plan $plan, string $billingType = 'CREDIT_CARD'): array
    {
        $customerId = $this->createOrUpdateCustomer($user);

        $cycle = match($plan->billing_cycle) {
            'monthly' => 'MONTHLY',
            'yearly' => 'YEARLY',
            'weekly' => 'WEEKLY',
            default => 'MONTHLY',
        };

        $response = $this->request('post', '/subscriptions', [
            'customer' => $customerId,
            'billingType' => $billingType,
            'value' => (float) $plan->price,
            'nextDueDate' => now()->addDay()->format('Y-m-d'),
            'cycle' => $cycle,
            'description' => "Assinatura {$plan->name} - SlideApp",
            'externalReference' => json_encode([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
            ]),
        ]);

        // Atualiza usuário
        $user->update([
            'asaas_subscription_id' => $response['id'],
            'subscription_status' => 'ACTIVE',
            'plan_id' => $plan->id,
        ]);

        return $response;
    }

    /**
     * Obter detalhes da assinatura
     */
    public function getSubscription(string $subscriptionId): array
    {
        return $this->request('get', "/subscriptions/{$subscriptionId}");
    }

    /**
     * Cancelar assinatura
     */
    public function cancelSubscription(string $subscriptionId): array
    {
        return $this->request('delete', "/subscriptions/{$subscriptionId}");
    }

    /**
     * Listar pagamentos de uma assinatura
     */
    public function getSubscriptionPayments(string $subscriptionId): array
    {
        return $this->request('get', "/subscriptions/{$subscriptionId}/payments");
    }

    /**
     * Criar cobrança única (para upgrade de plano)
     */
    public function createPayment(User $user, float $value, string $billingType, string $description): array
    {
        $customerId = $this->createOrUpdateCustomer($user);

        return $this->request('post', '/payments', [
            'customer' => $customerId,
            'billingType' => $billingType,
            'value' => $value,
            'dueDate' => now()->addDays(3)->format('Y-m-d'),
            'description' => $description,
            'externalReference' => (string) $user->id,
        ]);
    }

    /**
     * Obter detalhes de um pagamento
     */
    public function getPayment(string $paymentId): array
    {
        return $this->request('get', "/payments/{$paymentId}");
    }

    /**
     * Obter QR Code PIX de um pagamento
     */
    public function getPixQrCode(string $paymentId): array
    {
        return $this->request('get', "/payments/{$paymentId}/pixQrCode");
    }

    /**
     * Obter link de checkout para cartão
     */
    public function getCheckoutUrl(User $user, Plan $plan): string
    {
        $subscription = $this->createSubscription($user, $plan, 'UNDEFINED');
        
        // O primeiro pagamento da assinatura terá o link
        $payments = $this->getSubscriptionPayments($subscription['id']);
        
        if (!empty($payments['data'])) {
            return $payments['data'][0]['invoiceUrl'] ?? '';
        }

        return '';
    }

    /**
     * Atualizar assinatura (mudar plano)
     */
    public function updateSubscription(string $subscriptionId, Plan $plan): array
    {
        $cycle = match($plan->billing_cycle) {
            'monthly' => 'MONTHLY',
            'yearly' => 'YEARLY',
            'weekly' => 'WEEKLY',
            default => 'MONTHLY',
        };

        return $this->request('put', "/subscriptions/{$subscriptionId}", [
            'value' => (float) $plan->price,
            'cycle' => $cycle,
            'description' => "Assinatura {$plan->name} - SlideApp",
        ]);
    }
}

