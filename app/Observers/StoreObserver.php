<?php

namespace App\Observers;

use App\Models\Store;
use \Illuminate\Http\Request;
use App\Repositories\LocationRepository;

class StoreObserver
{
    public function created(Store $store)
    {
        request()->replace(
            array_merge(
                //  Set request location fields as top level key/value pairs
                request()->input('location'),

                //  Set this store as the owning store for this location
                //  Set this authenticated user as the location creator
                [
                    'store_id' => $store->id,
                    'user_id' => auth()->user()->id
                ]
            )
        );

        //  Create a new location using the location key/value pairs
        resolve(LocationRepository::class)->create(request());
    }

    public function updated(Store $store)
    {
        //
    }

    public function deleted(Store $store)
    {
        //  Foreach location
        foreach($store->locations as $location) {

            //  Delete location
            $location->forceDelete();

        }
    }

    public function restored(Store $store)
    {
        //
    }

    public function forceDeleted(Store $store)
    {
    }
}
