<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\Currency;
use App\Traits\ItemLineTrait;
use App\Models\Base\BaseModel;
use App\Casts\Status\ProductLineStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductLine extends BaseModel
{
    use HasFactory, ItemLineTrait;

    protected $table = 'product_lines';

    const ALLOWED_QUANTITY_PER_ORDER = ['limited', 'unlimited'];
    CONST STOCK_QUANTITY_TYPE = ['limited', 'unlimited'];

    protected $casts = [
        'is_free' => 'boolean',
        'on_sale' => 'boolean',
        'has_price' => 'boolean',
        'is_cancelled' => 'boolean',
        'detected_changes' => 'array',
        'cancellation_reasons' => 'array',
    ];

    protected $tranformableCasts = [
        'unit_sale_discount_percentage' => Percentage::class,
        'unit_profit_percentage' => Percentage::class,
        'unit_loss_percentage' => Percentage::class,
        'sale_discount_total' => Money::class,
        'is_free' => ProductLineStatus::class,
        'unit_sale_discount' => Money::class,
        'unit_regular_price' => Money::class,
        'unit_sale_price' => Money::class,
        'currency' => Currency::class,
        'unit_profit' => Money::class,
        'unit_price' => Money::class,
        'grand_total' => Money::class,
        'sub_total' => Money::class,
        'unit_loss' => Money::class,
        'unit_cost' => Money::class,
    ];

    protected $fillable = [

        /*  General Information  */
        'name', 'description',

        /*  Tracking Information  */
        'sku', 'barcode',

        /*  Pricing Information  */
        'is_free', 'currency', 'unit_regular_price', 'on_sale', 'unit_sale_price',
        'unit_sale_discount', 'unit_sale_discount_percentage', 'has_price',
        'unit_price', 'unit_cost', 'unit_profit', 'unit_profit_percentage',
        'unit_loss', 'unit_loss_percentage', 'sale_discount_total',
        'grand_total', 'sub_total',

        /*  Quantity Information  */
        'quantity', 'original_quantity',

        /*  Cancellation Information  */
        'is_cancelled', 'cancellation_reasons',

        /*  Detected Changes Information  */
        'detected_changes',

        /*  Ownership Information  */
        'product_id', 'cart_id', 'location_id'

    ];

    /****************************
     *  RELATIONSHIPS           *
     ***************************/

    /**
     *  Returns the associated product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
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
