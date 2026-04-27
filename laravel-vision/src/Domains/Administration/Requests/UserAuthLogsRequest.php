<?php

namespace Administration\Requests;

use Administration\Dtos\UserAuthLogsQueryDto;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for paginating auth log entries of a single user.
 */
final class UserAuthLogsRequest extends FormRequest
{
    private const int DEFAULT_PER_PAGE = 15;
    private const int MAX_PER_PAGE = 100;

    /**
     * @return bool true when the caller holds the users.view permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('users.view') === true;
    }

    /**
     * @return array<string, string> validation rules
     */
    public function rules(): array
    {
        return [
            'user' => 'required|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:' . self::MAX_PER_PAGE,
        ];
    }

    /**
     * @return array<string, mixed> validation input merged with the route user id
     */
    public function validationData(): array
    {
        return array_merge($this->all(), ['user' => $this->route('user')]);
    }

    /**
     * @return UserAuthLogsQueryDto DTO consumed by the service
     */
    public function getDto(): UserAuthLogsQueryDto
    {
        return new UserAuthLogsQueryDto(
            userId: (int) $this->route('user'),
            perPage: (int) $this->input('per_page', self::DEFAULT_PER_PAGE),
        );
    }
}
