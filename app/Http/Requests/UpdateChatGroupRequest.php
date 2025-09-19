<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChatGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $chatGroup = $this->route('chatGroup'); // {chatGroup:uuid}

        if (! $user || ! $chatGroup) {
            return false;
        }

        // Only the creator can update the group
        return (int) $chatGroup->creator_id === (int) $user->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'memberIds' => ['nullable', 'array'],
            'memberIds.*' => ['integer', 'exists:users,id'],
        ];
    }
}
