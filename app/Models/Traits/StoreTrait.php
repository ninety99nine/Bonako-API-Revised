<?php

namespace App\Models\Traits;

use App\Models\Traits\Base\BaseTrait;

/**
 *  Allows the model to define fields that are transformable
 *  for consumption by third-party sources. This allows us
 *  to convienently decide which properties we would like
 *  to share and which we avoid sharing.
 */
trait StoreTrait
{
    use BaseTrait;
}
