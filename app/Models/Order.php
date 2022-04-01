<?php

namespace App\Models;

use App\Models\Location;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends BaseModel
{
    use HasFactory;

    protected $fillable = ['number', 'amount', 'location_id'];

    /****************************
     *  RELATIONSHIPS           *
     ***************************/

    /**
     * Get the Location that owns the Order
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
