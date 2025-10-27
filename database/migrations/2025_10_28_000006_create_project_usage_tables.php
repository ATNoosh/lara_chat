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
        Schema::create('project_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            
            // Date tracking (monthly snapshots)
            $table->date('period_date'); // First day of the month
            
            // Usage metrics
            $table->integer('total_end_users')->default(0);
            $table->integer('active_end_users')->default(0); // Users who sent a message
            $table->integer('total_conversations')->default(0);
            $table->integer('active_conversations')->default(0);
            $table->integer('total_messages')->default(0);
            $table->integer('api_requests')->default(0);
            $table->integer('websocket_connections')->default(0);
            
            // Storage (in bytes)
            $table->bigInteger('storage_used')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['project_id', 'period_date']);
            $table->unique(['project_id', 'period_date']);
        });

        // API request logs (for rate limiting and analytics)
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('api_key_id')->nullable()->constrained('api_keys')->onDelete('set null');
            
            // Request info
            $table->string('endpoint');
            $table->string('method', 10);
            $table->integer('response_code');
            $table->integer('response_time_ms')->nullable();
            
            // Client info
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamp('created_at');
            
            // Indexes
            $table->index(['project_id', 'created_at']);
            $table->index('api_key_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
        Schema::dropIfExists('project_usage');
    }
};
