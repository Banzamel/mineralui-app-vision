<?php

namespace Shared\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait attaching automatic population of created_by and updated_by fields with the logged-in user ID.
 *
 * @mixin Model
 */
trait GenealogyTrait
{
    /**
     * Hooks model events - on creation sets created_by and updated_by, on update sets only updated_by.
     *
     * @return void
     */
    protected static function bootGenealogy(): void
    {
        static::creating(function ($model) {
            $model->created_by = auth()->id();
            $model->updated_by = auth()->id();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }
}
