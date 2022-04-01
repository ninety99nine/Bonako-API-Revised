<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Repositories\StoreRepository;
use App\Http\Requests\Models\DeleteRequest;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Location\CreateLocationRequest;
use App\Http\Requests\Models\Store\CreateStoreRequest;
use App\Http\Requests\Models\Store\UpdateStoreRequest;

class StoreController extends BaseController
{
    /**
     *  @var StoreRepository
     */
    protected $repository;

    public function index(Request $request)
    {
        return response($this->repository->get()->transform(), 200);
    }

    public function create(CreateStoreRequest $request)
    {
        return response($this->repository->create($request)->transform(), 201);
    }

    public function show(Store $store)
    {
        return response($this->repository->setModel($store)->transform(), 200);
    }

    public function update(UpdateStoreRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->update($request)->transform(), 200);
    }

    public function confirmDelete(Store $store)
    {
        return response($this->repository->setModel($store)->generateDeleteConfirmationCode(), 200);
    }

    public function delete(DeleteRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->delete(), 204);
    }

    public function showLocations(Store $store)
    {
        return response($this->repository->setModel($store)->showLocations(), 200);
    }

    public function createLocation(CreateLocationRequest $request, Store $store)
    {
        return response($this->repository->setModel($store)->createLocation($request)->transform(), 201);
    }
}
