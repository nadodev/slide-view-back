<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            
            // Dados do Asaas
            $table->string('asaas_payment_id')->unique();
            $table->string('asaas_subscription_id')->nullable();
            
            // Dados do pagamento
            $table->decimal('value', 10, 2);
            $table->decimal('net_value', 10, 2)->nullable(); // Valor líquido após taxas
            $table->string('billing_type'); // BOLETO, CREDIT_CARD, PIX
            $table->string('status'); // PENDING, RECEIVED, CONFIRMED, OVERDUE, REFUNDED, etc.
            
            // Datas
            $table->date('due_date');
            $table->timestamp('payment_date')->nullable();
            $table->timestamp('confirmed_date')->nullable();
            
            // Informações adicionais
            $table->string('invoice_url')->nullable();
            $table->string('bank_slip_url')->nullable();
            $table->string('pix_qr_code')->nullable();
            $table->text('pix_copy_paste')->nullable();
            
            // Descrição
            $table->string('description')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index('status');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

