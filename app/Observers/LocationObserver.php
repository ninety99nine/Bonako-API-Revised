<?php

namespace App\Observers;

use App\Models\Location;
use Illuminate\Support\Facades\DB;
use App\Repositories\LocationRepository;

class LocationObserver
{
    public function created(Location $location)
    {
        //  Add the user as a team member
        resolve(LocationRepository::class)->setModel($location)->addCreator(auth()->user()->id);
    }

    public function updated(Location $location)
    {
        //
    }

    public function deleted(Location $location)
    {
        //  Foreach product
        foreach($location->products as $product) {

            //  Delete product
            $product->forceDelete();

        }

        //  Delete the location user association
        DB::table('location_user')->where('location_id', $location->id)->delete();
    }

    public function restored(Location $location)
    {
        //
    }

    public function forceDeleted(Location $location)
    {
    }
}
