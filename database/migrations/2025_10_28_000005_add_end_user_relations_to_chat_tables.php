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
        // Update chat_messages to support end_users
        Schema::table('chat_messages', function (Blueprint $table) {
            // Add end_user_id column (nullable for backward compatibility)
            $table->foreignId('end_user_id')->nullable()->after('sender_id')->constrained('end_users')->onDelete('cascade');
            $table->index('end_user_id');
            
            // Keep sender_id nullable for admin users who might reply
            $table->foreignId('sender_id')->nullable()->change();
        });

        // Update chat_group_members to support end_users
        Schema::table('chat_group_members', function (Blueprint $table) {
            // Add end_user_id column
            $table->foreignId('end_user_id')->nullable()->after('user_id')->constrained('end_users')->onDelete('cascade');
            $table->index('end_user_id');
            
            // Make user_id nullable (for end users)
            $table->foreignId('user_id')->nullable()->change();
            
            // Add member type to differentiate between admin and end_user
            $table->enum('member_type', ['admin', 'end_user'])->default('end_user')->after('end_user_id');
            $table->index('member_type');
        });

        // Update chat_groups to track creator type
        Schema::table('chat_groups', function (Blueprint $table) {
            $table->enum('creator_type', ['admin', 'end_user'])->default('end_user')->after('creator_id');
            $table->foreignId('creator_end_user_id')->nullable()->after('creator_type')->constrained('end_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropForeign(['end_user_id']);
            $table->dropColumn('end_user_id');
        });

        Schema::table('chat_group_members', function (Blueprint $table) {
            $table->dropForeign(['end_user_id']);
            $table->dropColumn(['end_user_id', 'member_type']);
        });

        Schema::table('chat_groups', function (Blueprint $table) {
            $table->dropForeign(['creator_end_user_id']);
            $table->dropColumn(['creator_type', 'creator_end_user_id']);
        });
    }
};
