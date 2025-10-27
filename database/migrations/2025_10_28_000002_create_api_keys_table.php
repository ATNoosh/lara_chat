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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            
            // Key identification
            $table->string('name'); // Descriptive name (e.g., "Production API Key")
            $table->string('key_prefix', 20); // Visible prefix (e.g., "pk_live_")
            $table->string('key_hash'); // Hashed API key (like password)
            $table->string('key_hint', 10); // Last few characters for display
            
            // Permissions and scopes
            $table->json('scopes')->nullable(); // ['conversations:read', 'conversations:write', 'users:manage']
            
            // Environment
            $table->enum('environment', ['test', 'live'])->default('live');
            
            // Security
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_used_ip')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Rate limiting
            $table->integer('rate_limit_per_minute')->default(60);
            
            $table->timestamps();
            
            // Indexes
            $table->index('project_id');
            $table->index('key_prefix');
            $table->index(['is_active', 'expires_at']);
            $table->index('last_used_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
