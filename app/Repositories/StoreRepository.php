<?php

namespace App\Repositories;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class StoreRepository extends BaseRepository
{
    //  protected $modelClass = Store::class;
    //  protected $modelName = 'Store';

    //  protected $paginate = false;
    //  protected $transform = false;

    public function __construct(Request $request)
    {
        /**
         *  If the request is performed under the "auth.user.stores" or the
         *  "auth.user.stores.show" route naming convention, then we can
         *  safely assume that the stores collected must be related to
         *  the current auth user. We must indicate this by targeting
         *  the current auth user stores.
         */
        if( $request->routeIs(['auth.user.stores', 'auth.user.stores.show']) ) {

            /**
             *  To archieve this, we must query only stores that have locations where the
             *  current authenticated user has been assigned.
             */
            $this->model = Store::whereHas('locations.users', function (Builder $query) {
                $query->where('location_user.user_id', request()->user()->id);
            });

        }

        /**
         *  If the request is performed under the 'auth.user.*' route naming convention,
         *  then we can safely assume that the user is attempting to perform an action
         *  on an existing resource e.g show, update, delete
         */
        if( $request->routeIs('auth.user.stores.*') ) {

            //  Add the current authenticated user id
            $request->merge([
                'user_id' => request()->user()->id
            ]);

        }

        //  Run the base constructor
        parent::__construct();
    }
}
