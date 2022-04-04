<?php

namespace App\Models;

use App\Models\Location;
use App\Traits\OrderTrait;
use App\Models\Base\BaseModel;
use App\Traits\Base\BaseTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends BaseModel
{
    use HasFactory, BaseTrait, OrderTrait;

    const PAYMENT_STATUSES = ['Paid', 'Partially Paid', 'Refunded', 'Unpaid'];
    const DELIVERY_STATUSES = ['Delivered', 'Undelivered'];

    protected $fillable = [

        /*  Status Information  */
        'payment_status', 'delivery_status',

        /*  Cancellation Information  */
        'is_cancelled', 'cancellation_reason',

        /*  Delivery Information  */
        'delivery_confirmation_code', 'delivery_verified',
        'delivery_verified_at', 'delivery_verified_by_user_id',

        /*  Customer Information  */
        'customer_id',

        /*  Ownership Information  */
        'location_id'

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'delivery_confirmation_code',
    ];

    /****************************
     *  RELATIONSHIPS           *
     ***************************/

    /**
     *  Get the Cart assigned to this Order
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::morphOne
     */
    public function shoppingCart()
    {
        return $this->morphOne(Cart::class, 'owner');
    }

    /**
     * Get the Customer that owns the Order
     */
    public function customer()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the Location that owns the Order
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected $appends = ['number'];

    public function getNumberAttribute()
    {
        return str_pad($this->id, 5, 0, STR_PAD_LEFT);
    }
}
