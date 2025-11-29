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
        Schema::table('presentations', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('status');
            $table->string('share_token', 32)->nullable()->unique()->after('is_public');
            $table->boolean('allow_embed')->default(false)->after('share_token');
            $table->timestamp('shared_at')->nullable()->after('allow_embed');
            $table->integer('view_count')->default(0)->after('shared_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presentations', function (Blueprint $table) {
            $table->dropColumn(['is_public', 'share_token', 'allow_embed', 'shared_at', 'view_count']);
        });
    }
};

