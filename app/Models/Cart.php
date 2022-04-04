<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\Currency;
use App\Models\Base\BaseModel;
use App\Traits\Base\BaseTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends BaseModel
{
    use HasFactory, BaseTrait;

    protected $casts = [
        'detected_changes' => 'array',
        'abandoned_status' => 'boolean',
        'products_arrangement' => 'array',
        'delivery_destination' => 'array',
        'allow_free_delivery' => 'boolean',
    ];

    protected $tranformableCasts = [
        'sub_total' => Money::class,
        'grand_total' => Money::class,
        'currency' => Currency::class,
        'delivery_fee' => Money::class,
        'coupon_discount_total' => Money::class,
        'sale_discount_total' => Money::class,
        'coupon_and_sale_discount_total' => Money::class,
    ];

    /**
     *  Always eager load the product lines and coupon lines
     */
    protected $with = ['productLines', 'couponLines'];

    protected $fillable = [

        /*  Pricing  */
        'currency', 'sub_total', 'coupon_discount_total', 'sale_discount_total',
        'coupon_and_sale_discount_total', 'grand_total',

        /*  Delivery  */
        'allow_free_delivery', 'delivery_fee', 'delivery_destination',

        /*  Product Line Totals  */
        'total_products', 'total_product_quantities',
        'total_cancelled_products', 'total_cancelled_product_quantities',
        'total_uncancelled_products', 'total_uncancelled_product_quantities',

        /*  Coupon Line Totals  */
        'total_coupons',

        /*  Changes  */
        'products_arrangement', 'detected_changes', 'abandoned_status',

        /*  Instant Cart  */
        'instant_cart_id',

        /*  Ownership  */
        'location_id', 'owner_id', 'owner_type'

    ];

    /****************************
     *  SCOPES                  *
     ***************************/

    /**
     *  Scope carts for a given location
     */
    public function scopeForLocation($query, $location)
    {
        return $query->where('location_id', $location instanceof Model ? $location->id : $location);
    }

    /**
     *  Scope carts with product lines and coupon lines
     */
    public function scopeHasSomething($query)
    {
        return $query->has('productLines')->orHas('couponLines');
    }

    /**
     *  Scope carts without product lines and coupon lines
     */
    public function scopeDoesntHaveAnything($query)
    {
        return $query->doesntHaveProductLines()->doesntHaveCouponLines();
    }

    /**
     *  Scope carts that have product lines
     */
    public function scopeHasProductLines($query)
    {
        return $query->has('productLines');
    }

    /**
     *  Scope carts that don't have product lines
     */
    public function scopeDoesntHaveProductLines($query)
    {
        return $query->doesntHave('productLines');
    }

    /**
     *  Scope carts that have coupon lines
     */
    public function scopeHasCouponLines($query)
    {
        return $query->has('couponLines');
    }

    /**
     *  Scope carts that don't have coupon lines
     */
    public function scopeDoesntHaveCouponLines($query)
    {
        return $query->doesntHave('couponLines');
    }

    /****************************
     *  RELATIONSHIPS           *
     ***************************/

    /**
     *  Returns the associated location
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     *  Returns the associated product lines
     */
    public function productLines()
    {
        return $this->hasMany(ProductLine::class);
    }

    /**
     *  Returns the associated coupon lines
     */
    public function couponLines()
    {
        return $this->hasMany(CouponLine::class);
    }
}
