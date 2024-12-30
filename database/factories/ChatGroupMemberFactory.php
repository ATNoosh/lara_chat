<?php

namespace Database\Factories;

use App\Models\ChatGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChatGroupMember>
 */
class ChatGroupMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chat_group_id' => ChatGroup::factory(),
            'user_id' => User::factory(),
        ];
    }
}
