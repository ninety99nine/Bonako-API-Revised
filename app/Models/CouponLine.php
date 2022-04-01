<?php

namespace App\Models;

use App\Casts\Currency;
use App\Casts\Money;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Couponline extends BaseModel
{
    use HasFactory;

    protected $table = 'coupon_lines';

    const DISCOUNT_TYPES = Coupon::DISCOUNT_TYPES;

    protected $casts = [
        'hours_of_day' => 'array',
        'is_cancelled' => 'boolean',
        'end_datetime' => 'datetime',
        'offer_discount' => 'boolean',
        'days_of_the_week' => 'array',
        'detected_changes' => 'array',
        'days_of_the_month' => 'array',
        'start_datetime' => 'datetime',
        'months_of_the_year' => 'array',
        'cancellation_reasons' => 'array',
        'offer_free_delivery' => 'boolean',
        'activate_using_code' => 'boolean',
        'activate_for_new_customer' => 'boolean',
        'activate_using_usage_limit' => 'boolean',
        'activate_using_end_datetime' => 'boolean',
        'activate_using_hours_of_day' => 'boolean',
        'activate_using_start_datetime' => 'boolean',
        'activate_for_existing_customer' => 'boolean',
        'activate_using_days_of_the_week' => 'boolean',
        'activate_using_days_of_the_month' => 'boolean',
        'activate_using_months_of_the_year' => 'boolean',
        'activate_using_minimum_grand_total' => 'boolean',
        'activate_using_minimum_total_products' => 'boolean',
        'activate_using_minimum_total_product_quantities' => 'boolean',
    ];

    protected $tranformableCasts = [
        'currency' => Currency::class,
        'discount_fixed_rate' => Money::class,
        'minimum_grand_total' => Money::class,
        'discount_percentage_rate' => Percentage::class
    ];

    protected $fillable = [

            /*  General Information */
            'name', 'description',

            /*  Offer Discount Information */
            'offer_discount', 'discount_type', 'discount_percentage_rate', 'discount_fixed_rate',

            /*  Offer Free Delivery Information */
            'offer_free_delivery',

            /*  Activation Information  */
            'activate_using_code', 'code',
            'activate_using_end_datetime', 'end_datetime',
            'activate_using_hours_of_day', 'hours_of_day',
            'activate_using_start_datetime', 'start_datetime',
            'activate_using_days_of_the_week', 'days_of_the_week',
            'activate_using_days_of_the_month', 'days_of_the_month',
            'activate_using_months_of_the_year', 'months_of_the_year',
            'activate_using_minimum_total_products', 'minimum_total_products',
            'activate_for_new_customer', 'activate_for_existing_customer',
            'activate_using_usage_limit', 'limited_quantity', 'used_quantity',
            'activate_using_minimum_grand_total', 'currency', 'minimum_grand_total',
            'activate_using_minimum_total_product_quantities', 'minimum_total_product_quantities',

            /*  Cancellation Information  */
            'is_cancelled', 'cancellation_reasons',

            /*  Detected Changes Information  */
            'detected_changes',

            /*  Ownership  */
            'coupon_id', 'cart_id', 'location_id'

    ];

    /****************************
     *  SCOPES                  *
     ***************************/

    /**
     *  Scope coupons for a given location
     */
    public function scopeForLocation($query, $location)
    {
        return $query->where('location_id', $location instanceof Model ? $location->id : $location);
    }

    /****************************
     *  RELATIONSHIPS           *
     ***************************/

    /**
     *  Returns the associated coupon
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     *  Returns the associated cart
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     *  Returns the associated location
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
