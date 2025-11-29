<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'asaas_payment_id',
        'asaas_subscription_id',
        'value',
        'net_value',
        'billing_type',
        'status',
        'due_date',
        'payment_date',
        'confirmed_date',
        'invoice_url',
        'bank_slip_url',
        'pix_qr_code',
        'pix_copy_paste',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'net_value' => 'decimal:2',
            'due_date' => 'date',
            'payment_date' => 'datetime',
            'confirmed_date' => 'datetime',
        ];
    }

    /**
     * Usuário dono do pagamento
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Plano relacionado ao pagamento
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Verifica se o pagamento está confirmado
     */
    public function isConfirmed(): bool
    {
        return in_array($this->status, ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH']);
    }

    /**
     * Verifica se está pendente
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Verifica se está vencido
     */
    public function isOverdue(): bool
    {
        return $this->status === 'OVERDUE';
    }

    /**
     * Retorna o nome legível do tipo de pagamento
     */
    public function getBillingTypeLabelAttribute(): string
    {
        return match($this->billing_type) {
            'BOLETO' => 'Boleto',
            'CREDIT_CARD' => 'Cartão de Crédito',
            'PIX' => 'PIX',
            'DEBIT_CARD' => 'Cartão de Débito',
            default => $this->billing_type,
        };
    }

    /**
     * Retorna o nome legível do status
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'PENDING' => 'Pendente',
            'RECEIVED' => 'Recebido',
            'CONFIRMED' => 'Confirmado',
            'OVERDUE' => 'Vencido',
            'REFUNDED' => 'Reembolsado',
            'RECEIVED_IN_CASH' => 'Recebido em dinheiro',
            'REFUND_REQUESTED' => 'Reembolso solicitado',
            'CHARGEBACK_REQUESTED' => 'Chargeback solicitado',
            'CHARGEBACK_DISPUTE' => 'Disputa de chargeback',
            'AWAITING_CHARGEBACK_REVERSAL' => 'Aguardando reversão',
            'DUNNING_REQUESTED' => 'Negativação solicitada',
            'DUNNING_RECEIVED' => 'Negativação recebida',
            'AWAITING_RISK_ANALYSIS' => 'Em análise',
            default => $this->status,
        };
    }

    /**
     * Scope para pagamentos confirmados
     */
    public function scopeConfirmed($query)
    {
        return $query->whereIn('status', ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH']);
    }

    /**
     * Scope para pagamentos pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }
}

