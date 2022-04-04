<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Location;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Services\ShoppingCart\ShoppingCartService;
use App\Exceptions\LocationRoleDoesNotExistException;
use App\Exceptions\TeamMembersAlreadyInvitedException;
use App\Exceptions\CannotModifyOwnPermissionsException;
use App\Exceptions\TeamMemberInvitationAlreadyAcceptedException;
use App\Exceptions\TeamMemberInvitationAlreadyDeclinedException;
use App\Models\Cart;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class LocationRepository extends BaseRepository
{
    /**
     * @var \App\Models\User The location user
     */
    protected $locationUser;
    protected $requiresConfirmationBeforeDelete = true;

    public function __construct(Request $request)
    {
        //  Run the base constructor
        parent::__construct();

        /**
         *  If the request is performed by a route with the naming conventions listed below,
         *  then we can safely assume that the locations collected must be related to the
         *  current auth user or the route specified user. We must therefore target
         *  locations where the user is strictly a member.
         */

        /**
         *  For Auth routes that match (Used by Authenticated User) e.g
         *
         *  auth/user/locations/{location}
         *  auth/user/locations/{location}/orders
         *  auth/user/locations/{location}/products
         *  auth/user/locations/{location}/customers
         *  e.t.c ...
         */
        $requestOnAuthRoutes = $request->routeIs('auth.user.location.*');

        /**
         *  For User routes that match (Mostly used by Super Admin) e.g
         *
         *  users/{user}/locations/{location}
         *  users/{user}/locations/{location}/orders
         *  users/{user}/locations/{location}/products
         *  users/{user}/locations/{location}/customers
         *  e.t.c ...
         *
         *  IMPORTANT NOTE BELOW !!!
         *  ------------------------
         *
         *  We don't yet have these routes implemented ... so we need to make
         *  time to create them within the routes/api/v1/locations.php file
         */
        $requestOnUserRoutes = $request->routeIs('user.location.*');

        if($requestOnAuthRoutes || $requestOnUserRoutes) {

            //  In the case of Auth routes
            if( $requestOnAuthRoutes ) {

                //  Get the current authenticated user as the location user
                $this->locationUser = request()->user();

            //  In the case of User routes routes
            }elseif( $requestOnUserRoutes ) {

                //  Get the route specified user as the location user
                $this->locationUser = ($user = request()->user) instanceof Model ? $user : User::find($user);

            }

            //  If we have a user
            if( $this->locationUser ) {

                /**
                 *  Query locations where the current authenticated user
                 *  or the route specified user has been assigned as
                 *  a team member
                 */
                $this->model = Location::whereHas('users', function (Builder $query) {
                    $query->where('location_user.user_id', $this->locationUser->id);
                });

            }

        }
    }

    /**
     *  Return the ProductRepository instance
     *
     *  @return ProductRepository
     */
    public function productRepository()
    {
        return resolve(ProductRepository::class);
    }

    /**
     *  Return the CartRepository instance
     *
     *  @return CartRepository
     */
    public function cartRepository()
    {
        return resolve(CartRepository::class);
    }

    /**
     *  Create the user's location shopping cart
     */
    public function createShoppingCart()
    {
        /**
         * @var \App\Models\User $user
         */
        $user = auth()->user();

        /**
         *  Inspect and return the current shopping cart instance.
         *  This cart instance is simply a mockup of the current
         *  user's cart before it is saved to the database
         */
        $inspectedCart = resolve(ShoppingCartService::class)->startInspection();

        /**
         *  Search for an existing shopping cart from the database that belongs
         *  to this user for the given location. This shopping cart must be
         *  completely empty (Must not have product lines or coupon lines)
         */
        $recoveredCart = $user->shoppingCart()->doesntHaveAnything()->forLocation($this->model)->first();

        //  If we found an empty shopping cart (Lets use this recovered cart)
        if( $recoveredCart ) {

            return $this->cartRepository()->setModel($recoveredCart)->update(

                /**
                 *  Update the recovered cart using the inspected shopping cart.
                 *  This should update the pricing totals correctly as well as
                 *  other important details such as item quantities e.t.c
                 */
                $inspectedCart

            /**
             *  Attempt to create and associate the product lines and coupon lines to this
             *  recovered cart if they exist. In either case return the cart instance.
             */
            )->createProductAndCouponLines();

        }

        /**
         *  Create a new shopping cart instance for the user (Save to database).
         *  Setting the cart model to $user->shoppingCart() will allow us to
         *  create a cart that recognises the specified user as the owner
         *  (The owner_id and owner_type fields are filled automatically)
         */
        return $this->cartRepository()->setModel($user->shoppingCart())->create(

            //  Create a new cart using the inspected shopping cart
            $inspectedCart

        );
    }

    /**
     *  Calculate the user's location shopping cart.
     *  This means that we can workout the totals
     *  without creating a cart.
     */
    public function calculateShoppingCart()
    {
        /**
         *  Inspect and return the current shopping cart instance.
         *  This cart instance is simply a mockup of the current
         *  user's cart before it is saved to the database
         */
        $inspectedCart = resolve(ShoppingCartService::class)->startInspection();

        //  Lets return the shopping cart as part of the cart respositoty instance
        return $this->cartRepository()->setModel($inspectedCart);
    }

    /**
     *  Update the user's location shopping cart
     */
    public function updateShoppingCart(Cart $shoppingCart)
    {
        /**
         *  Inspect and return the current shopping cart instance.
         *  This cart instance is simply a mockup of the current
         *  user's cart before it is saved to the database
         */
        $inspectedCart = resolve(ShoppingCartService::class)->startInspection();

        return $this->cartRepository()->setModel($shoppingCart)->update(

            /**
             *  Update the shopping cart using the inspected shopping cart.
             *  This should update the pricing totals correctly as well as
             *  other important details such as item quantities e.t.c
             */
            $inspectedCart->toArray()

        /**
         *  Attempt to update the associated product lines and coupon lines to this
         *  recovered cart if they exist. In either case return the cart instance.
         */
        )->updateProductAndCouponLines();
    }

    /**
     *  Empty the user's location shopping cart
     */
    public function emptyShoppingCart(Cart $shoppingCart)
    {
        return $this->cartRepository()->setModel($shoppingCart)->empty();
    }

    /**
     *  Empty the user's location shopping cart
     */
    public function convertShoppingCart(Cart $shoppingCart)
    {
        return $this->cartRepository()->setModel($shoppingCart)->convert();
    }

    /**
     *  Show the location products
     */
    public function showProducts()
    {
        $products = $this->model->products()->isNotVariation();

        return $this->productRepository()->setModel($products)->get();
    }

    /**
     *  Create the location product
     */
    public function createProduct(Request $request)
    {
        $request->merge([
            'currency' => $this->model->store->currency,
            'location_id' => $this->model->id,
            'user_id' => auth()->user()->id
        ]);

        return $this->productRepository()->create($request);
    }

    /**
     *  Show the location team members. Either
     *  show all the team members or those
     *  that are invited.
     */
    public function showTeamMembers()
    {
        //  If we want team members that have been invited to this location
        if( request()->filled('accepted_invitation') ) {

            //  Query the users of this location that have not accepted / declined their invitation
            $users = $this->model->users()->whereHas('locations', function (Builder $query) {

                $query->where('location_user.location_id', $this->model->id)->where('location_user.accepted_invitation', request()->input('accepted_invitation'));

            });

        //  If we want all team members that have been assigned to this location
        }else {

            $users = $this->model->users();

        }

        return resolve(UserRepository::class)->setModel($users)->get();
    }

    /**
     *  Show the location team member
     */
    public function showTeamMember(User $user)
    {
        return resolve(UserRepository::class)->setModel($user)->showProfile();
    }

    /**
     *  Invite team members to this location
     */
    public function inviteTeamMembers()
    {
        $mobileNumbers = request()->input('mobile_numbers');

        //  Get the ids of users that are not assigned to this location
        $notAssignedUserIds = User::whereIn('mobile_number', $mobileNumbers)->whereDoesntHave('locations', function (Builder $query) {

            $query->where('location_user.location_id', $this->model->id);

        })->pluck('id')->toArray();

        /**
         *  If we supplied one or more numbers but returned no user ids,
         *  then it means that the numbers provided represent users
         *  that have already been invited.
         */
        if( count($notAssignedUserIds) === 0 ) {

            throw new TeamMembersAlreadyInvitedException;

        }else {

            //  Add users to this location
            $this->addOrUpdateTeamMembers($notAssignedUserIds);

            /**
             *  If we supplied one or more numbers but returned lesser user ids,
             *  then it means that one of the numbers provided represents the
             *  creator of this location.
             */
            if( ($mobileNumbers > count($notAssignedUserIds)) ) {

                return ['message' => 'Invitations sent successfully, but other members were already invited before.'];

            }else{

                return ['message' => 'Invitations sent successfully'];
            }

        }
    }

    /**
     *  Accept invitation to join location
     */
    public function acceptInvitation()
    {
        if( $this->checkInvitationAcceptedStatus() ){

            throw new TeamMemberInvitationAlreadyAcceptedException;

        }elseif( $this->checkInvitationDeclinedStatus() ){

            throw new TeamMemberInvitationAlreadyDeclinedException('This invitation has already been declined and cannot be accepted. Request the store manager to resend the invitation again.');

        }else{

            //  Mark the invitation as accepted
            $this->updateInvitationStatus('Yes');

            //  Fire team-member-invitation-accepted event to notify other team-members (those that can manage teams)

            return ['message' => 'Invitation accepted successfully'];

        }
    }

    /**
     *  Decline invitation to join location
     */
    public function declineInvitation()
    {
        if( $this->checkInvitationAcceptedStatus() ){

            throw new TeamMemberInvitationAlreadyAcceptedException('This invitation has already been accepted and cannot be declined. Withdraw from this location instead.');

        }elseif( $this->checkInvitationDeclinedStatus() ){

            throw new TeamMemberInvitationAlreadyDeclinedException;

        }else{

            //  Mark the invitation as declined
            $this->updateInvitationStatus('No');

            //  Fire team-member-invitation-declined event to notify other team-members (those that can manage teams)

            return ['message' => 'Invitation declined successfully'];

        }
    }

    /**
     *  Check if the user has already accepted this invitation
     */
    public function checkInvitationAcceptedStatus()
    {
        return $this->checkInvitationStatus('Yes');
    }

    /**
     *  Check if the user has already accepted this invitation
     */
    public function checkInvitationDeclinedStatus()
    {
        return $this->checkInvitationStatus('No');
    }

    /**
     *  Check if the user's invitation state
     */
    public function checkInvitationStatus($state)
    {
        return DB::table('location_user')
                ->where('accepted_invitation', $state)
                ->where('location_id', $this->model->id)
                ->where('user_id', $this->locationUser->id)->exists();
    }

    /**
     * Update the user's invitation state
     */
    public function updateInvitationStatus($state)
    {
        return DB::table('location_user')
                ->where('location_id', $this->model->id)
                ->where('user_id', $this->locationUser->id)->update([
                    'accepted_invitation' => $state
                ]);
    }

    /**
     *  Get user permissions for this location model instance
     */
    public function showMyPermissions()
    {
        $user = $this->model->users()->where('users.id', auth()->user()->id)->first();

        if( $user ){

            $role = $user->pivot->role;
            $permissions = $user->pivot->permissions;

            return [
                'role' => $role,
                'permissions' => $this->extractPermissions($permissions)
            ];

        }else{

            //  Throw exception if the user does not exist
            throw new AccessDeniedHttpException;

        }
    }

    /**
     *  Update user permissions for this location model instance
     */
    public function updateTeamMemberPermissions(User $user)
    {
        //  Deny the action of modifying your own permissions
        if( $user->id === auth()->user()->id ) throw new CannotModifyOwnPermissionsException;

        //  Add user's permissions to this location
        $this->addOrUpdateTeamMembers($user, $user->pivot->accepted_invitation);

        return ['message' => 'Permissions updated successfully'];
    }

    /**
     *  Provide the permission grant names in exchange
     *  of detailed permission details.
     */
    public function extractPermissions($permissions = [])
    {
        return collect($permissions)->contains('*')
            //  Get every permission available except the "*" permission
            ? collect(Location::PERMISSIONS)->filter(fn($permission) => $permission['grant'] !== '*')->values()
            //  Get only the specified permissions
            : collect($permissions)->map(function($permission) {
                return collect(Location::PERMISSIONS)->filter(
                    fn($locationPermission) => $locationPermission['grant'] == $permission
                )->first();
            })->filter();
    }

    /**
     *  Add a single user as creator of this location
     *
     *  @var integer $user_id
     *  @return void
     */
    public function addCreator($user_id)
    {
        $this->addOrUpdateTeamMembers($user_id, 'Yes', ['*'], 'Creator');
    }

    /**
     *  Add a single user or multiple users as admins to this location
     *
     *  @var integer | array $user_ids
     *  @return void
     */
    public function addAdmins($user_ids = [])
    {
        $this->addOrUpdateTeamMembers($user_ids, null, ['*'], 'Admin');
    }

    /**
     *  Add or update a single or multiple users on this location.
     *  This allows us to:
     *
     *  (1) Assign new users to this location with a given role and permissions
     *  (2) Update existing user roles and permissions
     *
     *  @param integer | \App\Models\User | array<int> $user_ids
     *  @param string | null $accepted_invitation e.g Yes, No, Not specified
     *  @param array | null $permissions e.g ['*'] or ['manage orders', 'manage customers']
     *  @param string | null $role e.g 'Admin'
     *  @return void
     */
    public function addOrUpdateTeamMembers($user_ids = [], $accepted_invitation = null, $permissions = [], $role = null)
    {
        if($user_ids instanceof Model) {

            $user_ids = [$user_ids->id];

        }elseif(is_int($user_ids)) {

            $user_ids = [$user_ids];

        }

        if( is_array($user_ids) && !empty($user_ids) ) {

            //  Set the default role name
            $defaulfRole = 'Team Member';

            //  Set the role to the default role if no value is indicated
            if( empty($role) ) $role = $defaulfRole;

            //  Check if this location role exists
            $roleDoesNotExist = collect(Location::ROLES)->contains($role) == false;

            if( $roleDoesNotExist ) throw new LocationRoleDoesNotExistException("The specified location role of $role does not exist");

            //  Capture the permission to be set (Prefer the parameter shared permissions over the request permissions)
            $permissions = count($permissions) ? $permissions : (request()->filled('permissions') ? request()->input('permissions') : []);

            //  Check if this location permissions exist
            $nonExistingPermissions = collect($permissions)->filter(function($currPermission) {

                //  Return permissions that are not granted by our location permissions
                return collect(Location::PERMISSIONS)->contains('grant', $currPermission) == false;

            })->join(', ', ' and ');

            if( $nonExistingPermissions ) throw new LocationRoleDoesNotExistException("The specified location permission ($nonExistingPermissions".(Str::contains($nonExistingPermissions, 'and') ? ') do not': ') does not')." exist");

            //  If we have granted the users the ability to manage everything
            if( collect($permissions)->contains('*') ) {

                /**
                 *  Ignore other permissions added and keep "*" permission
                 *  since it means that we can manage everything
                 */
                $permissions = ["*"];

                //  If this member reflects to be assigned the default role
                if( $role == $defaulfRole ) {

                    /**
                     *  Then change the role to "Admin"
                     *  since we can manage everything
                     */
                    $role = 'Admin';

                }

            }

            //  Set the accepted invitation to "Not specified" if no value is indicated
            if( empty($accepted_invitation) ) $accepted_invitation = 'Not specified';

            //  Remove the users with their old roles and permissions
            $this->model->users()->detach($user_ids);

            //  Assign the users with their new roles and permissions
            $this->model->users()->attach($user_ids, [
                'accepted_invitation' => $accepted_invitation,
                'permissions' => $permissions,
                'created_at' => now(),
                'updated_at' => now(),
                'role' => $role
            ]);

        }
    }

}
