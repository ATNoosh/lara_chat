<?php

use App\Constants\AppConstants;
use App\Models\ChatGroup;
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
        Schema::create('chat_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(true)->default(AppConstants::NEW_GROUP_NAME);
            $table->unsignedBigInteger('creator_id')->nullable(true)->default(0);
            $table->enum('type', ChatGroup::TYPES)->default(ChatGroup::TYPE_SIMPLE);
            $table->boolean('is_private')->nullable(false)->default(false);

            $table->foreign('creator_id')->references('id')->on('users')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_groups');
    }
};
