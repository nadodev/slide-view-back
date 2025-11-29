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
        Schema::create('slide_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slide_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('version_number');
            $table->string('title')->nullable();
            $table->longText('content');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->string('change_description')->nullable();
            $table->timestamps();

            $table->index(['slide_id', 'version_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slide_versions');
    }
};

