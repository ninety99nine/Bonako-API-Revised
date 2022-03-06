<?php

namespace App\Models;

use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['number', 'amount', 'location_id'];

    /**
     * Get the Location that owns the Order
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
