<?php

namespace Installer\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Installer\Dtos\FirstCameraDto;

/**
 * Request validating first camera data in the install wizard.
 */
final class CreateFirstCameraRequest extends FormRequest
{
    /**
     * The installer endpoint is public.
     *
     * @return bool Always true.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * First camera validation rules.
     *
     * @return array<string, string> Validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:512',
            'ip' => 'nullable|ip',
            'stream_url' => 'nullable|string|max:500',
            'stream_login' => 'nullable|string|max:255',
            'stream_password' => 'nullable|string|max:255',
        ];
    }

    /**
     * Builds a DTO with first camera data.
     *
     * @return FirstCameraDto Camera DTO.
     */
    public function getDto(): FirstCameraDto
    {
        return new FirstCameraDto(
            name: $this->input('name'),
            displayName: $this->input('display_name'),
            address: $this->input('address'),
            ip: $this->input('ip'),
            streamUrl: $this->input('stream_url'),
            streamLogin: $this->input('stream_login'),
            streamPassword: $this->input('stream_password'),
        );
    }
}
