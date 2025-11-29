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
        Schema::table('users', function (Blueprint $table) {
            $table->string('asaas_customer_id')->nullable()->after('avatar');
            $table->string('asaas_subscription_id')->nullable()->after('asaas_customer_id');
            $table->string('subscription_status')->nullable()->after('asaas_subscription_id'); // active, overdue, canceled
            $table->timestamp('subscription_next_due_date')->nullable()->after('subscription_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'asaas_customer_id',
                'asaas_subscription_id',
                'subscription_status',
                'subscription_next_due_date',
            ]);
        });
    }
};

