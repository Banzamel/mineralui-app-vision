<?php

namespace Installer\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Installer\Dtos\DatabaseConfigDto;

/**
 * Request validating saving database data to the .env file in the install wizard.
 */
final class SaveDatabaseRequest extends FormRequest
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
     * Validation rules for connection parameters.
     *
     * @return array<string, string> Validation rules.
     */
    public function rules(): array
    {
        return [
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'database' => 'required|string|max:128',
            'username' => 'required|string|max:128',
            'password' => 'nullable|string|max:255',
        ];
    }

    /**
     * Builds a DTO with connection data.
     *
     * @return DatabaseConfigDto Database configuration DTO.
     */
    public function getDto(): DatabaseConfigDto
    {
        return new DatabaseConfigDto(
            host: $this->input('host'),
            port: (int) $this->input('port'),
            database: $this->input('database'),
            username: $this->input('username'),
            password: (string) $this->input('password', ''),
        );
    }
}
