<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\User;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Repositories\LocationRepository;
use App\Http\Requests\Models\DeleteRequest;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Cart\CreateCartRequest;
use App\Http\Requests\Models\Cart\UpdateCartRequest;
use App\Http\Requests\Models\Cart\CalculateCartRequest;
use App\Http\Requests\Models\Product\CreateProductRequest;
use App\Http\Requests\Models\Location\UpdateLocationRequest;
use App\Http\Requests\Models\Location\ShowTeamMembersRequest;
use App\Http\Requests\Models\Location\InviteTeamMembersRequest;
use App\Http\Requests\Models\Location\UpdateTeamMemberPermissionsRequest;

class LocationController extends BaseController
{
    /**
     *  @var LocationRepository
     */
    protected $repository;

    public function index(Request $request)
    {
        return response($this->repository->get()->transform(), 200);
    }

    public function show(Location $location)
    {
        return response($this->repository->setModel($location)->transform(), 200);
    }

    public function update(UpdateLocationRequest $request, Location $location)
    {
        return response($this->repository->setModel($location)->update($request->all())->transform(), 200);
    }

    public function confirmDelete(Location $location)
    {
        return response($this->repository->setModel($location)->generateDeleteConfirmationCode(), 200);
    }

    public function delete(DeleteRequest $request, Location $location)
    {
        return response($this->repository->setModel($location)->delete(), 204);
    }

    public function showProducts(Location $location)
    {
        return response($this->repository->setModel($location)->showProducts()->transform(), 200);
    }

    public function createProduct(CreateProductRequest $request, Location $location)
    {
        return response($this->repository->setModel($location)->createProduct($request)->transform(), 201);
    }

    public function showMyPermissions(Location $location)
    {
        return response($this->repository->setModel($location)->showMyPermissions(), 200);
    }

    public function showTeamMembers(ShowTeamMembersRequest $request, Location $location)
    {
        return response($this->repository->setModel($location)->showTeamMembers()->transform(), 200);
    }

    public function showTeamMember(ShowTeamMembersRequest $request, Location $location, User $user)
    {
        return response($this->repository->setModel($location)->showTeamMember($user), 200);
    }

    public function inviteTeamMembers(InviteTeamMembersRequest $request, Location $location)
    {
        return response($this->repository->setModel($location)->inviteTeamMembers(), 200);
    }

    public function acceptInvitation(Location $location)
    {
        return response($this->repository->setModel($location)->acceptInvitation(), 200);
    }

    public function declineInvitation(Location $location)
    {
        return response($this->repository->setModel($location)->declineInvitation(), 200);
    }

    public function updateTeamMemberPermissions(UpdateTeamMemberPermissionsRequest $request, Location $location, User $user)
    {
        return response($this->repository->setModel($location)->updateTeamMemberPermissions($user), 200);
    }

    public function createShoppingCart(CreateCartRequest $request, Location $location)
    {
        return response($this->repository->setModel($location)->createShoppingCart()->transform(), 201);
    }

    public function calculateShoppingCart(CalculateCartRequest $request, Location $location)
    {
        return response($this->repository->setModel($location)->calculateShoppingCart()->transform(), 200);
    }

    public function updateShoppingCart(UpdateCartRequest $request, Location $location, Cart $cart)
    {
        return response($this->repository->setModel($location)->updateShoppingCart($cart)->transform(), 200);
    }

    public function emptyShoppingCart(Location $location, Cart $cart)
    {
        return response($this->repository->setModel($location)->emptyShoppingCart($cart)->transform(), 200);
    }

    public function convertShoppingCart(Location $location, Cart $cart)
    {
        return response($this->repository->setModel($location)->convertShoppingCart($cart)->transform(), 200);
    }

}
