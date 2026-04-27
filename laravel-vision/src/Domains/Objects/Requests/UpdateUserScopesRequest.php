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
            // `present` (instead of `required`) — payload MUST carry the `scopes` key, but an
            // empty array is allowed (admin unchecked all cameras → service delete-alls + inserts
            // nothing). `required` would reject [] and break the "revoke all access" flow.
            'scopes' => ['present', 'array'],
            'scopes.*.type' => ['required', 'string', Rule::in(ScopeType::values())],
            'scopes.*.scope_id' => ['required', 'string', 'max:255'],
        ];
    }
}
