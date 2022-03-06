<?php

namespace App\Models;

use App\Models\Traits\UserTrait;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Pivots\LocationUser;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UserTrait;

    protected $casts = [
        'mobile_number_verified_at' => 'datetime',
        'accepted_terms_and_conditions' => 'boolean'
    ];

    protected $fillable = [
        'first_name', 'last_name', 'password', 'mobile_number', 'mobile_number_verified_at',
        'accepted_terms_and_conditions'
    ];

    protected $unTransformableFields = [];

    protected $unTransformableAppends = [];

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

    /**
     *  Get the Locations that have been assigned to this User
     */
    public function locations()
    {
        return $this->belongsToMany(User::class, 'location_user')->withPivot(['role', 'default_location'])->use(LocationUser::class);
    }

    /**
     *  Append
     */
    protected $appends = [
        'name', 'requires_password', 'requires_mobile_number_verification'
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
}
