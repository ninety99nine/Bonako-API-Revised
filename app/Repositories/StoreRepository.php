<?php

namespace App\Repositories;

use App\Models\Store;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class StoreRepository extends BaseRepository
{
    //  protected $modelClass = Store::class;
    //  protected $modelName = 'Store';

    //  protected $paginate = false;
    //  protected $transform = false;

    protected $requiresConfirmationBeforeDelete = true;
    protected $updateIgnoreFields = ['accepted_golden_rules', 'user_id'];

    public function __construct(Request $request)
    {
        //  Run the base constructor
        parent::__construct();

        /**
         *  If the request is performed under the "auth.user.stores.show" or the
         *  "auth.user.store.*" route naming convention, then we can safely assume
         *  that the stores collected must be related to the current auth user.
         *  We must indicate this by targeting the current auth user stores.
         */
        if( $request->routeIs(['auth.user.stores.show', 'auth.user.store.*']) ) {

            /**
             *  To archieve this, we must query only stores that have locations where the
             *  current authenticated user has been assigned.
             */
            $this->model = Store::whereHas('locations.users', function (Builder $query) {
                $query->where('location_user.user_id', request()->user()->id);
            });

        }
    }

    /**
     *  Update existing store
     */
    public function update($data = [])
    {
        //  If we are not registered with a bank
        if(isset( $data['registered_with_bank'] ) && strtolower($data['registered_with_bank']) != 'Yes'){

            //  Remove the bank entry if previously specified
            $data['banking_with'] = Arr::last(Store::BANKING_WITH);

        }

        //  If we are not registered with CIPA
        if(isset( $data['registered_with_cipa'] ) && strtolower($data['registered_with_cipa']) != 'Yes'){

            //  Remove the CIPA registration type entry if previously specified
            $data['registered_with_cipa_as'] = Arr::last(Store::REGISTERED_WITH_CIPA_AS);

        }

        //  Update normally
        return parent::update($data);

    }

    /**
     *  Get store locations
     */
    public function showLocations()
    {
        return resolve(LocationRepository::class)->setModel($this->model->locations())->get();
    }

    /**
     *  Create store location
     */
    public function createLocation(Request $request)
    {
        $request->merge([
            'store_id' => $this->model->id,
            'user_id' => auth()->user()->id
        ]);

        return resolve(LocationRepository::class)->create($request);
    }


}
