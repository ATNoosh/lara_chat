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
        Schema::create('end_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            
            // User identification
            $table->string('user_identifier')->nullable(); // Client's internal user ID
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            
            // User type
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_verified')->default(false);
            
            // Custom metadata from client
            $table->json('metadata')->nullable(); // Any custom fields client wants to store
            
            // Session and activity tracking
            $table->string('session_token')->nullable(); // For widget sessions
            $table->timestamp('last_seen_at')->nullable();
            $table->string('last_seen_ip')->nullable();
            $table->string('user_agent')->nullable();
            
            // Location info (optional)
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('timezone')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('project_id');
            $table->index(['project_id', 'user_identifier']); // Composite for quick lookup
            $table->index(['project_id', 'email']);
            $table->index('is_anonymous');
            $table->index('last_seen_at');
            $table->index('created_at');
            
            // Unique constraint: one user_identifier per project
            $table->unique(['project_id', 'user_identifier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('end_users');
    }
};
