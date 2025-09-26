<?php

namespace Tests\Feature\Repositories;

use App\Models\ChatGroupMember;
use App\Models\User;
use App\Repositories\ChatGroupRepository;
use Tests\TestCase;

class ChatGroupRepositoriesTest extends TestCase
{
    private $currentGroup;

    public function createRandomFaceToFaceGroup()
    {
        $creator = User::factory()->create();
        $secondUser = User::factory()->create();

        $repository = app(ChatGroupRepository::class);

        return $repository->createFaceToFaceGroup($creator->id, $secondUser->id);
    }

    /**
     * A basic feature test example.
     */
    public function test_create_face_to_face_group(): void
    {
        $newGroup = $this->createRandomFaceToFaceGroup();
        $this->assertModelExists($newGroup);
        foreach ($newGroup->members ?? [] as $member) {
            $this->assertDatabaseHas(
                ChatGroupMember::class,
                [
                    'chat_group_id' => $newGroup->id,
                    'user_id' => $member->id,
                ]
            );
        }
        $this->assertEquals($newGroup?->members?->count() ?? 0, 2);
    }
}
