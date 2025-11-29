<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use App\Services\AsaasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected AsaasService $asaas;

    public function __construct(AsaasService $asaas)
    {
        $this->asaas = $asaas;
    }

    /**
     * Obter status da assinatura do usuário
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = [
            'has_subscription' => !empty($user->asaas_subscription_id),
            'subscription_status' => $user->subscription_status,
            'plan' => $user->plan ? [
                'id' => $user->plan->id,
                'name' => $user->plan->name,
                'slug' => $user->plan->slug,
                'price' => $user->plan->price,
                'billing_cycle' => $user->plan->billing_cycle,
                'features' => $user->plan->features,
            ] : null,
            'next_due_date' => $user->subscription_next_due_date,
            'expires_at' => $user->plan_expires_at,
        ];

        // Se tem assinatura, buscar detalhes
        if ($user->asaas_subscription_id) {
            try {
                $subscription = $this->asaas->getSubscription($user->asaas_subscription_id);
                $data['subscription_details'] = [
                    'status' => $subscription['status'],
                    'value' => $subscription['value'],
                    'next_due_date' => $subscription['nextDueDate'],
                    'billing_type' => $subscription['billingType'],
                ];
            } catch (\Exception $e) {
                Log::error('Erro ao buscar assinatura', ['error' => $e->getMessage()]);
            }
        }

        return response()->json($data);
    }

    /**
     * Criar checkout para assinatura
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'billing_type' => ['required', 'in:BOLETO,CREDIT_CARD,PIX'],
        ]);

        $user = $request->user();
        $plan = Plan::findOrFail($validated['plan_id']);

        if ($plan->isFree()) {
            return response()->json([
                'message' => 'Não é possível criar assinatura para plano gratuito.',
            ], 400);
        }

        // Cancelar assinatura anterior se existir
        if ($user->asaas_subscription_id) {
            try {
                $this->asaas->cancelSubscription($user->asaas_subscription_id);
            } catch (\Exception $e) {
                Log::warning('Erro ao cancelar assinatura anterior', ['error' => $e->getMessage()]);
            }
        }

        try {
            $subscription = $this->asaas->createSubscription(
                $user,
                $plan,
                $validated['billing_type']
            );

            // Buscar primeiro pagamento
            $payments = $this->asaas->getSubscriptionPayments($subscription['id']);
            $firstPayment = $payments['data'][0] ?? null;

            $response = [
                'message' => 'Assinatura criada com sucesso!',
                'subscription_id' => $subscription['id'],
            ];

            if ($firstPayment) {
                // Salvar pagamento no banco
                Payment::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'asaas_payment_id' => $firstPayment['id'],
                    'asaas_subscription_id' => $subscription['id'],
                    'value' => $firstPayment['value'],
                    'billing_type' => $firstPayment['billingType'],
                    'status' => $firstPayment['status'],
                    'due_date' => $firstPayment['dueDate'],
                    'invoice_url' => $firstPayment['invoiceUrl'] ?? null,
                    'bank_slip_url' => $firstPayment['bankSlipUrl'] ?? null,
                    'description' => "Assinatura {$plan->name}",
                ]);

                $response['payment'] = [
                    'id' => $firstPayment['id'],
                    'invoice_url' => $firstPayment['invoiceUrl'] ?? null,
                    'bank_slip_url' => $firstPayment['bankSlipUrl'] ?? null,
                    'due_date' => $firstPayment['dueDate'],
                    'value' => $firstPayment['value'],
                ];

                // Se for PIX, buscar QR Code
                if ($validated['billing_type'] === 'PIX') {
                    try {
                        $pix = $this->asaas->getPixQrCode($firstPayment['id']);
                        $response['payment']['pix'] = [
                            'qr_code' => $pix['encodedImage'] ?? null,
                            'copy_paste' => $pix['payload'] ?? null,
                            'expiration_date' => $pix['expirationDate'] ?? null,
                        ];
                    } catch (\Exception $e) {
                        Log::warning('Erro ao buscar PIX QR Code', ['error' => $e->getMessage()]);
                    }
                }
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar assinatura: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancelar assinatura
     */
    public function cancel(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->asaas_subscription_id) {
            return response()->json([
                'message' => 'Você não possui assinatura ativa.',
            ], 400);
        }

        try {
            $this->asaas->cancelSubscription($user->asaas_subscription_id);

            // Voltar para plano free
            $freePlan = Plan::where('slug', 'free')->first();

            $user->update([
                'asaas_subscription_id' => null,
                'subscription_status' => 'canceled',
                'plan_id' => $freePlan?->id,
                'plan_expires_at' => now(), // Expira imediatamente ou pode deixar até o fim do período
            ]);

            return response()->json([
                'message' => 'Assinatura cancelada com sucesso.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao cancelar assinatura: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar histórico de pagamentos
     */
    public function payments(Request $request): JsonResponse
    {
        $user = $request->user();

        $payments = Payment::where('user_id', $user->id)
            ->with('plan:id,name,slug')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'payments' => $payments->items(),
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    /**
     * Obter detalhes de um pagamento
     */
    public function paymentDetails(Request $request, Payment $payment): JsonResponse
    {
        if ($payment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Pagamento não encontrado.'], 404);
        }

        $data = $payment->toArray();
        $data['plan'] = $payment->plan;
        $data['billing_type_label'] = $payment->billing_type_label;
        $data['status_label'] = $payment->status_label;

        // Se PIX e pendente, buscar QR Code atualizado
        if ($payment->billing_type === 'PIX' && $payment->isPending()) {
            try {
                $pix = $this->asaas->getPixQrCode($payment->asaas_payment_id);
                $data['pix'] = [
                    'qr_code' => $pix['encodedImage'] ?? null,
                    'copy_paste' => $pix['payload'] ?? null,
                    'expiration_date' => $pix['expirationDate'] ?? null,
                ];
            } catch (\Exception $e) {
                Log::warning('Erro ao buscar PIX', ['error' => $e->getMessage()]);
            }
        }

        return response()->json($data);
    }

    /**
     * Webhook do Asaas
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $event = $payload['event'] ?? null;

        Log::info('Asaas Webhook', ['event' => $event, 'payload' => $payload]);

        try {
            switch ($event) {
                case 'PAYMENT_CONFIRMED':
                case 'PAYMENT_RECEIVED':
                    $this->handlePaymentConfirmed($payload['payment']);
                    break;

                case 'PAYMENT_OVERDUE':
                    $this->handlePaymentOverdue($payload['payment']);
                    break;

                case 'PAYMENT_DELETED':
                case 'PAYMENT_REFUNDED':
                    $this->handlePaymentRefunded($payload['payment']);
                    break;

                case 'PAYMENT_CREATED':
                case 'PAYMENT_UPDATED':
                    $this->handlePaymentUpdated($payload['payment']);
                    break;
            }

            return response()->json(['received' => true]);

        } catch (\Exception $e) {
            Log::error('Webhook Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Processar pagamento confirmado
     */
    protected function handlePaymentConfirmed(array $paymentData): void
    {
        $payment = Payment::where('asaas_payment_id', $paymentData['id'])->first();

        if ($payment) {
            $payment->update([
                'status' => $paymentData['status'],
                'net_value' => $paymentData['netValue'] ?? null,
                'payment_date' => $paymentData['paymentDate'] ?? now(),
                'confirmed_date' => now(),
            ]);

            // Ativar plano do usuário
            $user = $payment->user;
            if ($user && $payment->plan_id) {
                $user->update([
                    'plan_id' => $payment->plan_id,
                    'subscription_status' => 'active',
                    'plan_expires_at' => now()->addMonth(), // Ou calcular baseado no ciclo
                ]);
            }
        }
    }

    /**
     * Processar pagamento vencido
     */
    protected function handlePaymentOverdue(array $paymentData): void
    {
        $payment = Payment::where('asaas_payment_id', $paymentData['id'])->first();

        if ($payment) {
            $payment->update(['status' => 'OVERDUE']);

            // Atualizar status do usuário
            $user = $payment->user;
            if ($user) {
                $user->update(['subscription_status' => 'overdue']);
            }
        }
    }

    /**
     * Processar pagamento reembolsado/deletado
     */
    protected function handlePaymentRefunded(array $paymentData): void
    {
        $payment = Payment::where('asaas_payment_id', $paymentData['id'])->first();

        if ($payment) {
            $payment->update(['status' => $paymentData['status']]);
        }
    }

    /**
     * Atualizar dados do pagamento
     */
    protected function handlePaymentUpdated(array $paymentData): void
    {
        $payment = Payment::where('asaas_payment_id', $paymentData['id'])->first();

        if ($payment) {
            $payment->update([
                'status' => $paymentData['status'],
                'invoice_url' => $paymentData['invoiceUrl'] ?? $payment->invoice_url,
                'bank_slip_url' => $paymentData['bankSlipUrl'] ?? $payment->bank_slip_url,
            ]);
        } else {
            // Criar pagamento se não existir (pode ser de assinatura recorrente)
            $externalRef = json_decode($paymentData['externalReference'] ?? '{}', true);
            
            if (!empty($externalRef['user_id'])) {
                Payment::create([
                    'user_id' => $externalRef['user_id'],
                    'plan_id' => $externalRef['plan_id'] ?? null,
                    'asaas_payment_id' => $paymentData['id'],
                    'asaas_subscription_id' => $paymentData['subscription'] ?? null,
                    'value' => $paymentData['value'],
                    'billing_type' => $paymentData['billingType'],
                    'status' => $paymentData['status'],
                    'due_date' => $paymentData['dueDate'],
                    'invoice_url' => $paymentData['invoiceUrl'] ?? null,
                    'bank_slip_url' => $paymentData['bankSlipUrl'] ?? null,
                    'description' => $paymentData['description'] ?? null,
                ]);
            }
        }
    }
}

