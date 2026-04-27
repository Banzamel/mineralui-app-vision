<?php

namespace Objects\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * File upload request shared by VisionObject and Camera main-photo endpoints.
 * Accepts an `image` field — JPEG/PNG/WebP/GIF up to 5 MB.
 */
final class MainPhotoRequest extends FormRequest
{
    /**
     * @return bool true when the caller can manage objects/cameras (both share the same role).
     */
    public function authorize(): bool
    {
        return $this->user()?->can('objects.manage') === true
            || $this->user()?->can('cameras.manage') === true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'image' => 'required|image|max:5120',
        ];
    }
}
