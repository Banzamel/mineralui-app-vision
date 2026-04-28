<?php

namespace Objects\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Input validator for editing a camera — all fields are optional.
 */
class UpdateCameraRequest extends FormRequest
{
    /**
     * @return bool true when the caller holds the cameras.manage permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('cameras.manage') === true;
    }

    /**
     * Validation rules for the camera update.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'object_id' => ['sometimes', 'integer', 'exists:vision_objects,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'display_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:512'],
            'ip' => ['sometimes', 'nullable', 'ip'],
            'stream_url' => ['sometimes', 'string', 'max:1024'],
            'stream_login' => ['sometimes', 'nullable', 'string', 'max:255'],
            'stream_password' => ['sometimes', 'nullable', 'string', 'max:255'],
            'main_photo_path' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'motion_preview_enabled' => ['sometimes', 'boolean'],
        ];
    }
}
