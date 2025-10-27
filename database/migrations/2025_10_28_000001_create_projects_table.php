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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            
            // Plan and status
            $table->enum('plan', ['free', 'starter', 'pro', 'enterprise'])->default('free');
            $table->enum('status', ['active', 'suspended', 'cancelled'])->default('active');
            
            // Settings stored as JSON
            $table->json('settings')->nullable();
            
            // Usage limits (based on plan)
            $table->integer('max_end_users')->default(100); // Free plan limit
            $table->integer('max_messages_per_month')->default(1000);
            $table->integer('max_conversations')->default(50);
            
            // Domain whitelist for CORS and widget
            $table->json('allowed_domains')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('owner_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
