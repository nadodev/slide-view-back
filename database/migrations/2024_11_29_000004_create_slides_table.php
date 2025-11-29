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
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presentation_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0); // Ordem do slide na apresentação
            $table->string('title')->nullable();
            $table->longText('content'); // Conteúdo Markdown do slide
            $table->text('notes')->nullable(); // Notas do apresentador
            $table->json('metadata')->nullable(); // Metadados extras (layout, background, etc)
            $table->timestamps();

            $table->index(['presentation_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slides');
    }
};

