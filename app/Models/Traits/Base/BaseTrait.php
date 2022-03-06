<?php

namespace App\Models\Traits\Base;

/**
 *  Allows the model to define fields that are transformable
 *  for consumption by third-party sources. This allows us
 *  to convienently decide which properties we would like
 *  to share and which we avoid sharing.
 */
trait BaseTrait
{
    /**
     *  Return the transformable fields
     */
    public function getTransformableFields()
    {
        return collect($this->fillable)->except(empty($this->unTransformableFields) ? [] : $this->unTransformableFields)->toArray();
    }

    /**
     *  Return the transformable appends
     */
    public function getTransformableAppends()
    {
        return collect($this->appends)->except(empty($this->unTransformableAppends) ? [] : $this->unTransformableAppends)->toArray();
    }
}
