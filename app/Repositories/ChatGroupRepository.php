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
        if ($newChatGroup)
            return $newChatGroup;
        DB::beginTransaction();
        try {
            $newChatGroup = ChatGroup::updateOrCreate(
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

        return $newChatGroup;
    }

    public function getFaceToFaceeGroup(User $creator, User $secondUser): ChatGroup|null
    {
        $newChatGroup = ChatGroup::orWhere(function ($query) use ($creator, $secondUser) {
            $query->where('name', $creator->id . '_' . $secondUser->id)
                ->where('creator_id', $creator->id);
        })->orWhere(function ($query) use ($creator, $secondUser) {
            $query->where('name', $secondUser->id . '_' . $creator->id)
                ->where('creator_id', $secondUser->id);
        })->first();

        return $newChatGroup;
    }
}
