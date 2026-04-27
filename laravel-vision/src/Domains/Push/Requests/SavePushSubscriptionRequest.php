<?php

namespace Push\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Push\Dtos\PushSubscriptionDto;

/**
 * Validator for saving a web push subscription — endpoint may be any URL from a push provider.
 */
final class SavePushSubscriptionRequest extends FormRequest
{
    /**
     * @return bool true — saving a subscription only requires an authenticated user, enforced upstream
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>> validation rules
     */
    public function rules(): array
    {
        return [
            'endpoint' => ['required', 'string', 'max:2048'],
            'keys.p256dh' => ['required', 'string', 'max:512'],
            'keys.auth' => ['required', 'string', 'max:512'],
            'user_agent' => ['nullable', 'string', 'max:512'],
        ];
    }

    /**
     * @return PushSubscriptionDto DTO consumed by the service
     */
    public function getDto(): PushSubscriptionDto
    {
        return new PushSubscriptionDto(
            endpoint: (string) $this->input('endpoint'),
            p256dh: (string) $this->input('keys.p256dh'),
            auth: (string) $this->input('keys.auth'),
            userAgent: $this->input('user_agent') ?? $this->userAgent(),
        );
    }
}
