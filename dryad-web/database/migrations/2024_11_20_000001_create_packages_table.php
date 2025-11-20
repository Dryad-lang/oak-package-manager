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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->index();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('author');
            $table->string('author_email')->nullable();
            $table->string('license')->default('MIT');
            $table->json('keywords')->nullable();
            $table->string('homepage')->nullable();
            $table->json('repository')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('download_count')->default(0);
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();

            // Índices para performance
            $table->index(['is_active', 'download_count']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};