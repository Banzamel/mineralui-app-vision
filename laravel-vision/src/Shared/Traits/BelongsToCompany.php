<?php

namespace Shared\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Administration\Models\Company;
use Shared\Scopes\CompanyScope;

/**
 * Trait adding company ownership to a model - enables a global company filter and automatically fills in company_id.
 */
trait BelongsToCompany
{
    /**
     * Initializes the trait - registers the global company scope and sets company_id automatically when a new record is created.
     *
     * @return void
     */
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope());

        static::creating(function ($model) {
            if (empty($model->company_id)) {
                $user = auth()->user();
                if ($user && isset($user->company_id)) {
                    $model->company_id = $user->company_id;
                }
            }
        });
    }

    /**
     * Returns the company this record belongs to.
     *
     * @return BelongsTo Relation to the company model.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
