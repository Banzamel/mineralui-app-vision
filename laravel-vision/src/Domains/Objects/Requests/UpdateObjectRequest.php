<?php

namespace Objects\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Objects\Enums\ObjectType;

/**
 * Input validator for editing an object — all fields are optional.
 */
class UpdateObjectRequest extends FormRequest
{
    /**
     * @return bool true when the caller holds the objects.manage permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('objects.manage') === true;
    }

    /**
     * Validation rules for the object update.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:vision_objects,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', Rule::in(ObjectType::values())],
            'address' => ['sometimes', 'nullable', 'string', 'max:512'],
            'description' => ['sometimes', 'nullable', 'string'],
            'main_photo_path' => ['sometimes', 'nullable', 'string', 'max:1024'],
        ];
    }
}
