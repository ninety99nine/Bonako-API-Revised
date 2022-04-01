<?php

namespace App\Models\Base;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

/**
 *  Allows the model to define fields that are transformable
 *  for consumption by third-party sources. This allows us
 *  to convienently decide which properties we would like
 *  to share and which we avoid sharing.
 */
abstract class BaseModel extends Model
{
    /**
     *  Return the transformable fields
     */
    public function getTransformableFields()
    {
        return collect([

            'id', ...$this->fillable,

        //  Include the created_at conditionally
        ])->when(Schema::hasColumn($this->getTable(), 'created_at'), function ($collection, $value) {

            return $collection->push('created_at');

            //  Include the updated_at conditionally
        })->when(Schema::hasColumn($this->getTable(), 'updated_at'), function ($collection, $value) {

            return $collection->push('updated_at');

        })->except(empty($this->unTransformableFields) ? [] : $this->unTransformableFields)->toArray();
    }

    /**
     *  Return the transformable appends
     */
    public function getTransformableAppends()
    {
        return collect($this->appends)->except(empty($this->unTransformableAppends) ? [] : $this->unTransformableAppends)->toArray();
    }

    /**
     *  Return the transformable casts
     */
    public function getTranformableCasts()
    {
        return $this->tranformableCasts ?? [];
    }
}
