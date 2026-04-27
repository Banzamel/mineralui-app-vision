<?php

namespace Objects\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Objects\Enums\ScopeType;

/**
 * Input validator for bulk-setting scopes for a given user.
 * Frontend sends a list of (type, scope_id) pairs — the service deletes the old ones and inserts the new.
 */
class UpdateUserScopesRequest extends FormRequest
{
    /**
     * @return bool true when the caller holds the scopes.manage permission
     */
    public function authorize(): bool
    {
        return $this->user()?->can('scopes.manage') === true;
    }

    /**
     * Validation rules for the scopes payload.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // `present` (zamiast `required`) — payload MUSI zawierać klucz `scopes`,
            // ale dopuszczamy pustą tablicę (admin odznaczył wszystkie kamery → service
            // wykona delete-all + nic nie wstawia).
            'scopes' => ['present', 'array'],
            'scopes.*.type' => ['required', 'string', Rule::in(ScopeType::values())],
            'scopes.*.scope_id' => ['required', 'string', 'max:255'],
        ];
    }
}
