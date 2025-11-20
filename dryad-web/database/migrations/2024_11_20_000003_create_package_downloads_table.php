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
        Schema::create('package_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_version_id')->constrained('package_versions')->onDelete('cascade');
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('country')->nullable();
            $table->timestamp('downloaded_at');
            $table->timestamps();

            // Índices para estatísticas
            $table->index(['package_version_id', 'downloaded_at']);
            $table->index('downloaded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_downloads');
    }
};