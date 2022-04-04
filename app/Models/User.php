<?php

namespace App\Models;

use App\Models\Location;
use App\Traits\UserTrait;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Pivots\LocationUser;
use App\Models\Base\BaseAuthenticatable;
use App\Repositories\LocationRepository;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends BaseAuthenticatable /* Authenticatable */
{
    use HasApiTokens, HasFactory, Notifiable, UserTrait;

    protected $casts = [
        'mobile_number_verified_at' => 'datetime',
        'accepted_terms_and_conditions' => 'boolean',
        'is_super_admin' => 'boolean'
    ];

    protected $fillable = [
        'first_name', 'last_name', 'password', 'mobile_number', 'mobile_number_verified_at',
        'accepted_terms_and_conditions'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    /*
     *  Scope: Return users that are being searched using the mobile number
     */
    public function scopeSearchMobileNumber($query, $mobileNumber, $exact = true)
    {
        //  If we need an explicit match
        if($exact){

            return $query->where('mobile_number', $mobileNumber);

        //  If we need an implicit match
        }else{

            return $query->where('mobile_number', 'like', "%".$mobileNumber."%");

        }
    }

    /****************************
     *  RELATIONSHIPS           *
     ***************************/

    /**
     *  Get the Locations that have been assigned to this User
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function locations()
    {
        return $this->belongsToMany(Location::class, 'location_user')->withPivot(['id', 'role', 'permissions', 'default_location', 'accepted_invitation'])->using(LocationUser::class);
    }

    /**
     *  Get the Cart assigned to this User
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::morphOne
     */
    public function shoppingCart()
    {
        return $this->morphOne(Cart::class, 'owner')->latestOfMany();
    }

    /**
     *  Get the Orders that have been placed by this User
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::hasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected $appends = [
        'name', 'requires_password', 'requires_mobile_number_verification', 'location_association'
    ];

    public function getNameAttribute()
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function getRequiresPasswordAttribute()
    {
        return empty($this->password);
    }

    public function getRequiresMobileNumberVerificationAttribute()
    {
        return empty($this->mobile_number_verified_at);
    }

    public function getLocationAssociationAttribute()
    {
        if($this->pivot && ($this->pivot instanceOf LocationUser)) {

            $accepted_invitation_description = [
                'No' => 'The invitation has been declined',
                'Yes' => 'The invitation has been accepted',
                'Not specified' => 'The invitation is still waiting for a response'
            ];

            return [
                'role' => $this->pivot->role,
                'default_location' => $this->pivot->default_location,
                'accepted_invitation' => [
                    'status' => $this->pivot->accepted_invitation,
                    'description' => $accepted_invitation_description[$this->pivot->accepted_invitation]
                ],
                'permissions' => resolve(LocationRepository::class)->extractPermissions($this->pivot->permissions)
            ];

        }else{

            return [];

        }
    }
}
