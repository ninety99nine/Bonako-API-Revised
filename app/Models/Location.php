<?php

namespace App\Models;

use App\Models\User;
use App\Models\Base\BaseModel;
use App\Models\Pivots\LocationUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends BaseModel
{
    use HasFactory;

    const ROLES = [
        'Creator', 'Admin', 'Team Member'
    ];

    const PERMISSIONS = [
        [
            'name' => 'Manage everything',
            'grant' => '*',
            'description' => 'Permission to manage everything'
        ],
        [
            'name' => 'Manage orders',
            'grant' => 'manage orders',
            'description' => 'Permission to manage orders'
        ],
        [
            'name' => 'Manage products',
            'grant' => 'manage products',
            'description' => 'Permission to manage products'
        ],
        [
            'name' => 'Manage customers',
            'grant' => 'manage customers',
            'description' => 'Permission to manage customers'
        ],
        [
            'name' => 'Manage team members',
            'grant' => 'manage team members',
            'description' => 'Permission to manage team members'
        ],
        [
            'name' => 'Manage instant carts',
            'grant' => 'manage instant carts',
            'description' => 'Permission to manage instant carts'
        ],
        [
            'name' => 'Manage settings',
            'grant' => 'manage settings',
            'description' => 'Permission to manage location settings including updating or deleting location'
        ],
    ];

    const DEFAULT_OFFLINE_MESSAGE = 'We are currently offline';

    protected $casts = [
        'online' => 'boolean',
        'accepted_golden_rules' => 'boolean',
    ];

    protected $fillable = ['name', 'call_to_action', 'online', 'offline_message', 'user_id', 'store_id'];

    /****************************
     *  RELATIONSHIPS           *
     ***************************/

    /**
     * Get the Store that owns this Location
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     *  Returns the associated products that are not variations
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     *  Returns the associated coupons
     */
    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    /**
     *  Returns the associated carts
     */
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     *  Get the Users that have been assigned to this Location
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'location_user')->withPivot(['id', 'role', 'permissions', 'default_location', 'accepted_invitation'])->using(LocationUser::class);
    }






    /**
     * Get the Orders owned by the Location
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
