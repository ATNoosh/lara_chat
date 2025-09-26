<?php

namespace Database\Seeders;

use App\Models\ChatGroup;
use Illuminate\Database\Seeder;

class ChatGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ChatGroup::factory()
            ->count(100)
            ->hasChatMessages(50)
            ->create();
    }
}
