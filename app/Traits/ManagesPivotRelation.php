<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait ManagesPivotRelation
{
    protected function attachRelated(Model $model, string $relation, $relatedId): Model
    {
        $model->{$relation}()->syncWithoutDetaching([$relatedId]);
        return $model->load($relation);
    }

    protected function detachRelated(Model $model, string $relation, $relatedId): Model
    {
        $model->{$relation}()->detach($relatedId);
        return $model->load($relation);
    }
}