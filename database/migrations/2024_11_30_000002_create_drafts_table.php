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
        Schema::create('drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('presentation_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type')->default('presentation'); // presentation, slide
            $table->string('title')->nullable();
            $table->longText('content');
            $table->json('metadata')->nullable();
            $table->timestamp('last_saved_at');
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'presentation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drafts');
    }
};

