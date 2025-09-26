<?php

namespace App\Repositories;

use App\Constants\AppConstants;
use App\Models\ChatGroup;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ChatGroupRepository
{
    public function createFaceToFaceGroup(...$userIds): ?ChatGroup
    {
        $userIds = Arr::flatten(is_array($userIds) ? $userIds : func_get_args());
        if (count($userIds) !== 2) {
            throw new Exception('Face to face groups must have 2 members!');
        }
        $newChatGroup = $this->getFaceToFaceGroup($userIds);
        if ($newChatGroup) {
            return $newChatGroup;
        } else {
            DB::beginTransaction();
            try {
                $newChatGroup = $this->createGroup(
                    [
                        'creator_id' => $userIds[0],
                        'type' => ChatGroup::TYPE_FACE_TO_FACE,
                        'is_private' => true,
                    ]
                );
                $this->addMembersToGroup($newChatGroup->id, $userIds);
            } catch (Exception $exp) {
                DB::rollBack();
                dd($exp);

                return null;
            }
            DB::commit();
        }

        return $newChatGroup;
    }

    public function getFaceToFaceGroup(...$userIds): ?ChatGroup
    {
        $userIds = Arr::flatten(is_array($userIds) ? $userIds : func_get_args());

        $group = ChatGroup::faceToFace()->whereHas('members', function (Builder $query) use ($userIds) {
            $query->whereIn('user_id', $userIds);
        })->first();

        return $group;
    }

    public function createGroup(array $groupData): ?ChatGroup
    {
        $newChatGroup = ChatGroup::create(
            [
                'name' => $groupData['name'] ?? AppConstants::NEW_GROUP_NAME,
                'creator_id' => $groupData['creator_id'] ?? 0,
                'is_private' => $groupData['is_private'] ?? false,
                'type' => $groupData['type'] ?? ChatGroup::TYPE_SIMPLE,
            ]
        );

        return $newChatGroup;
    }

    public function createGroupWithMembers(int $creatorId, array $memberIds, array $groupData = []): ChatGroup
    {
        DB::beginTransaction();
        try {
            $group = $this->createGroup([
                'name' => $groupData['name'] ?? AppConstants::NEW_GROUP_NAME,
                'creator_id' => $creatorId,
                'is_private' => $groupData['is_private'] ?? false,
                'type' => $groupData['type'] ?? ChatGroup::TYPE_SIMPLE,
            ]);

            // Ensure creator is included
            $allMemberIds = array_unique(array_merge([$creatorId], $memberIds));
            $this->addMembersToGroup($group->id, $allMemberIds);

            DB::commit();

            return $group;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function addMembersToGroup(int $groupId, ...$userIds)
    {
        $userIds = Arr::flatten(is_array($userIds) ? $userIds : func_get_args());

        $group = ChatGroup::find($groupId);
        if (! $group) {
            throw new Exception('Group not found!');
        }

        $result = $group->members()->syncWithoutDetaching($userIds);

        return $result['attached'];
    }

    public function setGroupMembers(int $groupId, array $userIds): array
    {
        $group = ChatGroup::findOrFail($groupId);
        $result = $group->members()->sync($userIds);

        return $result['attached'];
    }
}
