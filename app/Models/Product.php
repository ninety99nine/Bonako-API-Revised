<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\Currency;
use App\Casts\StockQuantity;
use App\Traits\ProductTrait;
use App\Models\Base\BaseModel;
use App\Casts\StockQuantityType;
use App\Casts\Status\ProductStatus;
use App\Casts\AllowedQuantityPerOrder;
use Illuminate\Database\Eloquent\Model;
use App\Casts\MaximumAllowedQuantityPerOrder;
use App\Casts\Percentage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends BaseModel
{
    use HasFactory, ProductTrait;

    const ALLOWED_QUANTITY_PER_ORDER = ['limited', 'unlimited'];
    CONST STOCK_QUANTITY_TYPE = ['limited', 'unlimited'];

    protected $casts = [
        'is_free' => 'boolean',
        'on_sale' => 'boolean',
        'visible' => 'boolean',
        'has_price' => 'boolean',
        'has_stock' => 'boolean',
        'allow_variations' => 'boolean',
        'show_description' => 'boolean',
        'variant_attributes' => 'array',
        'allowed_quantity_per_order' => 'boolean'
    ];

    protected $tranformableCasts = [
        'maximum_allowed_quantity_per_order' => MaximumAllowedQuantityPerOrder::class,
        'allowed_quantity_per_order' => AllowedQuantityPerOrder::class,
        'unit_sale_discount_percentage' => Percentage::class,
        'stock_quantity_type' => StockQuantityType::class,
        'unit_profit_percentage' => Percentage::class,
        'unit_loss_percentage' => Percentage::class,
        'allow_variations' => ProductStatus::class,
        'show_description' => ProductStatus::class,
        'stock_quantity' => StockQuantity::class,
        'unit_sale_discount' => Money::class,
        'unit_regular_price' => Money::class,
        'is_free' => ProductStatus::class,
        'visible' => ProductStatus::class,
        'unit_sale_price' => Money::class,
        'currency' => Currency::class,
        'unit_profit' => Money::class,
        'unit_price' => Money::class,
        'unit_loss' => Money::class,
        'unit_cost' => Money::class
    ];

    protected $fillable = [

        /*  General Information  */
        'name', 'visible', 'show_description', 'description',

        /*  Tracking Information  */
        'sku', 'barcode',

        /*  Variation Information  */
        'allow_variations', 'variant_attributes',

        /*  Pricing Information  */
        'is_free', 'currency', 'unit_regular_price', 'on_sale', 'unit_sale_price',
        'unit_sale_discount', 'unit_sale_discount_percentage', 'has_price',
        'unit_price', 'unit_cost', 'unit_profit', 'unit_profit_percentage',
        'unit_loss', 'unit_loss_percentage',

        /*  Quantity Information  */
        'allowed_quantity_per_order', 'maximum_allowed_quantity_per_order',

        /*  Stock Information  */
        'has_stock', 'stock_quantity_type', 'stock_quantity',

        /*  Arrangement Information  */
        'arrangement',

        /*  Ownership Information  */
        'parent_product_id', 'user_id', 'location_id'

    ];

    /****************************
     *  SCOPES                  *
     ***************************/

    /**
     *  Scope products that support variations.
     *  This means that this product has
     *  different versions of itself
     */
    public function scopeSupportsVariations($query)
    {
        return $query->where('allow_variations', '1');
    }

    /**
     *  Scope products that does not support variations.
     *  This means that this product does not have
     *  different versions of itself
     */
    public function scopeDoesNotSupportVariations($query)
    {
        return $query->where('allow_variations', '0');
    }

    /**
     *  Scope products that are variations of other products
     */
    public function scopeIsVariation($query)
    {
        return $query->whereNotNull('parent_product_id');
    }

    /**
     *  Scope products that are not variations of other products
     */
    public function scopeIsNotVariation($query)
    {
        return $query->whereNull('parent_product_id');
    }

    /**
     *  Scope carts for a given location
     */
    public function scopeForLocation($query, $location)
    {
        return $query->where('location_id', $location instanceof Model ? $location->id : $location);
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
     *  Returns the associated product variations
     */
    public function variations()
    {
        return $this->hasMany(Product::class, 'parent_product_id');
    }

    /**
     *  Returns the product variables. These are the properties that
     *  make this product a variation e.g Size=Small, Color=Blue,
     *  and Material=Cotton are all variables that make this
     *  product variation different from other variations.
     */
    public function variables()
    {
        return $this->hasMany(Variable::class);
    }

}
