<?php

namespace App\Repositories;

use App\Models\ChatGroup;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class ChatGroupRepository
{
    public function createFaceToFaceeGroup(User $creator, User $secondUser): ChatGroup|null
    {
        $newChatGroup = $this->getFaceToFaceeGroup($creator, $secondUser);
        if ($newChatGroup) {
            return $newChatGroup;
        } else {
            DB::beginTransaction();
            try {
                $newChatGroup = ChatGroup::create(
                    [
                        'name' => $creator->id . '_' . $secondUser->id,
                        'creator_id' => $creator->id
                    ]
                );
                $newChatGroup->members()->attach([$creator->id, $secondUser->id]);
            } catch (Exception $exp) {
                DB::rollBack();
                return null;
            }
            DB::commit();
        }
        return $newChatGroup;
    }

    public function getFaceToFaceeGroup(User $creator, User $secondUser): ChatGroup|null
    {
        $group = ChatGroup::orWhere(function ($query) use ($creator, $secondUser) {
            $query->where('name', $creator->id . '_' . $secondUser->id)
                ->where('creator_id', $creator->id);
        })->orWhere(function ($query) use ($creator, $secondUser) {
            $query->where('name', $secondUser->id . '_' . $creator->id)
                ->where('creator_id', $secondUser->id);
        })->first();

        return $group;
    }

    public function createGroup(User $creator, string $name)
    {
        DB::beginTransaction();
        try {
            $newChatGroup = ChatGroup::create(
                [
                    'name' => $name,
                    'creator_id' => $creator->id
                ]
            );
            $newChatGroup->members()->attach($creator->id);
            DB::commit();
            
            return $newChatGroup;
        } catch (Exception $exp) {
            DB::rollBack();
            return null;
        }
    }
}
