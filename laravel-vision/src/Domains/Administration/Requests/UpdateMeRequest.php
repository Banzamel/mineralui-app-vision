<?php

namespace Administration\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for updating the currently logged-in user's profile.
 * Every field is optional — only the ones present are validated and applied.
 */
final class UpdateMeRequest extends FormRequest
{
    /**
     * @return bool true when the caller is authenticated (any logged-in user may edit their own profile)
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>> validation rules
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                Rule::unique('sec_users', 'email')->ignore($userId),
            ],
            'password' => ['sometimes', 'string', 'min:8'],
        ];
    }
}
