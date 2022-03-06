<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\StoreRepository;
use App\Http\Requests\Models\Store\GetStoreRequest;
use App\Http\Requests\Models\Store\CreateStoreRequest;

class StoreController extends Controller
{
    //  private $storeRepository;

    public function __construct(/* StoreRepository $storeRepository */) {

        /**
         *  The issue we are facing is that whenever we instantiate our
         *  StoreRepository class using dependency injection within
         *  the constructor, this runs before our middleware rules
         *  can be applied, therefore the resulting StoreRepository
         *  instance does not have access to the auth santum user
         *  since we have not yet run the middleware.
         *
         *  For now we are using the dependency injection within each
         *  method since we are sure that the middleware has executed
         *
         *  ------
         *  Q1: How can we run methods or perform dependency injection
         *      after running the constructor but before running the
         *      target method of the contoller. We could inject our
         *      StoreRepository in this special function.
         *
         *  Q2: How can we call a method in the controller constructor
         *      after the middlewares have fully be resolved.
         */

        //  $this->storeRepository = $this->storeRepository;
    }

    public function index(StoreRepository $storeRepository, Request $request)
    {
        return $storeRepository->get();
    }

    public function create(CreateStoreRequest $request, StoreRepository $storeRepository)
    {
        return response($storeRepository->create($request->all())->transform(), 201);
    }

    public function show(GetStoreRequest $request, StoreRepository $storeRepository, $id)
    {
        return $storeRepository->findAndTranform($id);
    }

    public function update(CreateStoreRequest $request, StoreRepository $storeRepository, $id)
    {
        return $storeRepository->update($id, $request->all())->transform();
    }

    public function delete(StoreRepository $storeRepository, $id)
    {
        return response($storeRepository->delete($id), 204);
    }
}
