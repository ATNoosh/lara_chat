<?php

namespace Database\Seeders;

use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()
            ->count(10)
            ->has(
                ChatGroup::factory()->count(10)->has(
                    ChatMessage::factory()->count(20)
                ),
                'createdChatGroups'
            )
            ->create();
    }
}
