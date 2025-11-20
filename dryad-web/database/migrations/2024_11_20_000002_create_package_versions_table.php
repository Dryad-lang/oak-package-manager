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
        Schema::create('package_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->string('version');
            $table->text('changelog')->nullable();
            $table->json('dependencies')->nullable();
            $table->json('dev_dependencies')->nullable();
            $table->string('download_url');
            $table->string('file_hash')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->boolean('is_prerelease')->default(false);
            $table->boolean('is_deprecated')->default(false);
            $table->integer('download_count')->default(0);
            $table->timestamp('published_at');
            $table->timestamps();

            // Chave única composta
            $table->unique(['package_id', 'version']);
            
            // Índices para performance
            $table->index(['package_id', 'is_prerelease', 'published_at']);
            $table->index('download_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_versions');
    }
};