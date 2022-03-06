<?php

namespace App\Models\Traits;

use App\Models\Store;
use App\Models\Traits\Base\BaseTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 *  Allows the model to define fields that are transformable
 *  for consumption by third-party sources. This allows us
 *  to convienently decide which properties we would like
 *  to share and which we avoid sharing.
 */
trait UserTrait
{
    use BaseTrait;

    /**
     *  Check if the current authenticated user is assigned to the given store
     */
    public function isAssignedToStore($store_id)
    {
        return Store::where('id', $store_id)->whereHas('locations.users', function (Builder $locations) {

            return $locations->where('location_user.user_id', request()->user()->id);

        })->exists();
    }
}
