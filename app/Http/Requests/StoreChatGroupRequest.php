<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Face-to-face mode
            'secondUserId' => [
                'required_without:memberIds',
                'nullable',
                'integer',
                'exists:users,id',
                'different:' . auth()->id()
            ],
            // Group mode
            'memberIds' => [
                'required_without:secondUserId',
                'nullable',
                'array',
            ],
            'memberIds.*' => [
                'integer',
                'exists:users,id',
                'different:' . auth()->id()
            ],
            'name' => ['nullable', 'string', 'max:255']
        ];
    }
}
