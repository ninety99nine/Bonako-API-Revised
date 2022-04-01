<?php

namespace App\Traits;

use App\Models\Location;
use App\Models\Store;
use App\Traits\Base\BaseTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait UserTrait
{
    use BaseTrait;

    public function isSuperAdmin()
    {
        return $this->is_super_admin;
    }

    /**
     *  Check if the current authenticated user is assigned to the given store
     */
    public function isAssignedToStore($store, $roles = [])
    {
        $id = $store instanceof Model ? $store->id : $store;

        return Store::where('id', $id)->whereHas('locations.users', function (Builder $locations) use ($roles) {

            return $locations->where('location_user.user_id', request()->user()->id)->whereIn('role', count($roles) ? $roles : Location::ROLES);

        })->exists();
    }

    /**
     *  Check if the current authenticated user has the given permissions on the location
     */
    public function hasLocationPermissionTo($location, $permission)
    {
        $location_id = $location instanceof Model ? $location->id : $location;

        //  Get the matching location
        if( ($location = $this->locations()->where('location_id', $location_id)->first()) && $permission ) {

            //  Check if the user has the given permissions on the location
            return collect($location->pivot->permissions)->contains(function($locationPermission) use ($permission) {

                //  Check if we have all permissions or atleast the permission required
                return ($locationPermission == '*') || (strtolower($locationPermission) == strtolower($permission));

            });

        }

        return false;
    }

}
