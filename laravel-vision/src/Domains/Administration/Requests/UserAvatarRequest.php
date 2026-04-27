<?php

namespace Administration\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for uploading a user's profile picture (avatar).
 * Verifies the caller's permissions and validates that the file is a proper image.
 */
final class UserAvatarRequest extends FormRequest
{
    /**
     * Checks whether the authenticated user is allowed to update users.
     *
     * @return bool true when the user holds the users.update permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('users.update') === true;
    }

    /**
     * Returns the validation rules for the avatar file (must be an image, max 2 MB).
     *
     * @return array<string, string> validation rules (field => rule)
     */
    public function rules(): array
    {
        return [
            'avatar' => 'required|image|max:2048',
        ];
    }
}
