<?php

namespace Tests\Feature\Repositories;

use App\Models\ChatGroup;
use App\Models\ChatGroupMember;
use App\Models\User;
use App\Repositories\ChatGroupRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChatGroupRepositoriesTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_create_one_by_one_group(): void
    {
        $creator = User::factory()->create();
        $secondUser = User::factory()->create();

        $repository = app(ChatGroupRepository::class);
        $newGroup = $repository->createOneByOneGroup($creator, $secondUser);

        $this->assertModelExists($newGroup);

        $this->assertDatabaseHas(
            ChatGroupMember::class,
            [
                'chat_group_id' => $newGroup->id,
                'user_id' => $creator->id
            ]
        );
        $this->assertDatabaseHas(
            ChatGroupMember::class,
            [
                'chat_group_id' => $newGroup->id,
                'user_id' => $secondUser->id
            ]
        );
    }
}
