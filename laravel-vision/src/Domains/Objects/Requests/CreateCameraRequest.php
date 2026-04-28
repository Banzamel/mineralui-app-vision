<?php

namespace Objects\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Input validator for creating a camera under an object.
 * RTSP password arrives in the plaintext "stream_password" field — the service encrypts it.
 */
class CreateCameraRequest extends FormRequest
{
    /**
     * @return bool true when the caller holds the cameras.manage permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('cameras.manage') === true;
    }

    /**
     * Validation rules for vision_cameras columns.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'object_id' => ['required', 'integer', 'exists:vision_objects,id'],
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:512'],
            'ip' => ['nullable', 'ip'],
            'stream_url' => ['nullable', 'string', 'max:1024'],
            'stream_login' => ['nullable', 'string', 'max:255'],
            'stream_password' => ['nullable', 'string', 'max:255'],
            'main_photo_path' => ['nullable', 'string', 'max:1024'],
            'motion_preview_enabled' => ['sometimes', 'boolean'],
        ];
    }
}
