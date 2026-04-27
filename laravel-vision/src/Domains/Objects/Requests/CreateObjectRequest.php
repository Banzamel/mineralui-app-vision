<?php

namespace Objects\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Objects\Enums\ObjectType;

/**
 * Input validator for creating an object in the Vision tree.
 */
class CreateObjectRequest extends FormRequest
{
    /**
     * @return bool true when the caller holds the objects.manage permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('objects.manage') === true;
    }

    /**
     * Validation rules for vision_objects columns.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', 'exists:vision_objects,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(ObjectType::values())],
            'address' => ['nullable', 'string', 'max:512'],
            'description' => ['nullable', 'string'],
            'main_photo_path' => ['nullable', 'string', 'max:1024'],
        ];
    }
}
