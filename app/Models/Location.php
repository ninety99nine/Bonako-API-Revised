<?php

namespace App\Models;

use App\Models\Pivots\LocationUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;

    const ROLES = [
        'Creator', 'Admin', 'Team Member'
    ];

    protected $fillable = ['name', 'store_id'];

    /**
     * Get the Store that owns this Location
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     *  Get the Users that have been assigned to this Location
     */
    public function users()
    {
        return $this->belongsToMany(Location::class, 'location_user')->withPivot(['role', 'default_location'])->use(LocationUser::class);
    }






    /**
     * Get the Orders owned by the Location
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
