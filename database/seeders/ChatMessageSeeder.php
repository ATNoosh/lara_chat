<?php

namespace Database\Seeders;

use App\Models\ChatMessage;
use Illuminate\Database\Seeder;

class ChatMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ChatMessage::factory()->count(100)->create();
    }
}
