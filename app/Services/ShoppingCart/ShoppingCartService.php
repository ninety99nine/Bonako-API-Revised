<?php

namespace App\Services\ShoppingCart;

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Couponline;
use App\Models\ProductLine;
use App\Traits\Base\BaseTrait;
use App\Exceptions\CartRequiresStoreException;
use App\Exceptions\CartRequiresLocationException;

/**
 *  Note that the shopping cart service is instantiated once.
 *  The service can only exist as one instance (Singleton)
 *
 *  Refer to our AppServiceProvider
 */
class ShoppingCartService
{
    use BaseTrait;

    protected $store;
    protected $location;
    protected $currency;
    public $existingCart;
    protected $subTotal = 0;
    public $cartProducts = [];
    protected $grandTotal = 0;
    protected $deliveryFee = 0;
    protected $relatedProducts;
    public $cartCouponCodes = [];
    protected $deliveryDestination;
    protected $locationCoupons = [];
    protected $detectedChanges = [];
    protected $saleDiscountTotal = 0;
    public $existingCouponLines = [];
    public $existingProductLines = [];
    public $specifiedCouponLines = [];
    public $specifiedProductLines = [];
    protected $couponDiscountTotal = 0;
    protected $allowFreeDelivery = false;
    public $totalSpecifiedCouponLines = 0;
    public $totalSpecifiedProductLines = 0;
    protected $deliveryDestinationName = [];
    protected $couponAndSaleDiscountTotal = 0;
    public $totalSpecifiedProductLineQuantities = 0;
    public $totalSpecifiedCancelledProductLines = 0;
    public $totalSpecifiedUnCancelledProductLines = 0;
    public $totalSpecifiedCancelledProductLineQuantities = 0;
    public $totalSpecifiedUncancelledProductLineQuantities = 0;

    public function __construct($location = null, $existingCart = null, $items = [])
    {
        //  Get the shopping location
        $this->location = request()->location;

        //  Get the shopping store
        $this->store = $this->location->store;

        //  Check that the cart shopping location exists
        if( !$this->location ) throw new CartRequiresLocationException;

        //  Check that the cart shopping store exists
        if( !$this->store ) throw new CartRequiresStoreException;

        //  Get the location coupons
        $this->locationCoupons = $this->location->coupons;

        //  Get the existing cart (If exists)
        $this->existingCart = request()->cart;

        //  If we have an existing cart
        if( $this->existingCart ) {

            //  Get the existing product lines of the cart (Saved on the database)
            $this->existingProductLines = $this->existingCart->productLines;

            //  Get the existing coupon lines of the cart (Saved on the database)
            $this->existingCouponLines = $this->existingCart->couponLines;

        }

        if( request()->filled('cart_products') ) {

            //  Get the shopping cart products
            $this->cartProducts = request()->input('cart_products');

        }

        if( request()->filled('cart_coupon_codes') ) {

            //  Get the shopping cart coupon codes
            $this->cartCouponCodes = request()->input('cart_coupon_codes');

        }

        if( request()->filled('delivery_destination_name') ) {

            //  Get the shopping cart delivery destination name
            $this->deliveryDestinationName = request()->input('delivery_destination_name');

        }
    }

    /**
     *  Empty the cart
     */
    public function emptyCart()
    {
        $this->cartProducts = [];
        $this->cartCouponCodes = [];
        $this->deliveryDestinationName = null;

        return $this;
    }

    /**
     *  Start the cart inspection to determine the cart totals
     *  and important cart changes before converting the
     *  current shopping cart into an order
     */
    public function startInspection()
    {
        //  Set the store currency
        $this->currency = $this->store->currency;

        //  Get the shopping cart product lines
        $this->specifiedProductLines = $this->getSpecifiedProductLines();

        //  Detect changes on the product lines
        $this->detectChangesOnProductLines();

        //  Get the specified coupon lines
        $this->specifiedCouponLines = $this->getSpecifiedCouponLines();

        //  Calculate the total product lines
        $this->totalSpecifiedProductLines = $this->countSpecifiedProductLines();

        //  Calculate the total product line quantities
        $this->totalSpecifiedProductLineQuantities = $this->countSpecifiedProductLineQuantities();

        //  Calculate the total cancelled product lines
        $this->totalSpecifiedCancelledProductLines = $this->countSpecifiedCancelledProductLines();

        //  Calculate the total cancelled product lines quantities
        $this->totalSpecifiedCancelledProductLineQuantities = $this->countSpecifiedCancelledProductLineQuantities();

        //  Calculate the total uncancelled product lines
        $this->totalSpecifiedUnCancelledProductLines = $this->countSpecifiedUnCancelledProductLines();

        //  Calculate the total uncancelled product lines quantities
        $this->totalSpecifiedUncancelledProductLineQuantities = $this->countSpecifiedUncancelledProductLineQuantities();

        //  Calculate the total coupons
        $this->totalSpecifiedCouponLines = $this->calculateTotalCoupons();

        //  Get the matching delivery destination
        $this->deliveryDestination = $this->getDeliveryDestination();

        //  Determine if we can offer free delivery
        $this->allowFreeDelivery = $this->offerFreeDelivery();

        //  Calculate and set the shopping cart totals
        $this->calculateAndSetTotals();

        //  Return a new shopping cart instance
        return new Cart([

            /*  Pricing  */
            'currency' => $this->currency,
            'sub_total' => $this->subTotal,
            'grand_total' => $this->grandTotal,
            'sale_discount_total' => $this->saleDiscountTotal,
            'coupon_discount_total' => $this->couponDiscountTotal,
            'coupon_and_sale_discount_total' => $this->couponAndSaleDiscountTotal,

            /*  Delivery  */
            'delivery_fee' => $this->deliveryFee,
            'allow_free_delivery' => $this->allowFreeDelivery,
            'delivery_destination' => $this->deliveryDestination,

            /*  Product Line Totals  */
            'total_products' => $this->totalSpecifiedProductLines,
            'total_product_quantities' => $this->totalSpecifiedProductLineQuantities,

            'total_cancelled_products' => $this->totalSpecifiedCancelledProductLines,
            'total_cancelled_product_quantities' => $this->totalSpecifiedCancelledProductLineQuantities,

            'total_uncancelled_products' => $this->totalSpecifiedUnCancelledProductLines,
            'total_uncancelled_product_quantities' => $this->totalSpecifiedUncancelledProductLineQuantities,

            /*  Coupon Line Totals  */
            'total_coupons' => $this->totalSpecifiedCouponLines,

            /*  Changes  */
            'abandoned_status',
            'products_arrangement',
            'detected_changes' => $this->detectedChanges,

            /*  Instant Cart  */
            'instant_cart_id',

            /*  Ownership  */
            'location_id' => $this->location->id

        ]);

    }

    public function getSpecifiedProductLines()
    {
        //  If we have the shopping cart products, then extract the product ids
        $cartProductIds = collect($this->cartProducts)->pluck('id')->toArray();

        //  If we have atleast one shopping cart product id
        if( count($cartProductIds) ) {

            //  Get the related products that match the specified shopping cart product ids for the given location
            $this->relatedProducts = Product::forLocation($this->location->id)
                                    ->doesNotSupportVariations()
                                    ->whereIn('id', $cartProductIds)
                                    ->get();

            //  Foreach related product
            return collect($this->relatedProducts)->map(function($relatedProduct) {

                //  Get the related product that matches the given shopping cart product id
                $cartProduct = collect($this->cartProducts)->first(fn($cartProduct) => $relatedProduct->id == $cartProduct['id']);

                //  If we have a related product
                if( $relatedProduct ) {

                    //  Set the quantity otherwise default to "1" (Original quantity before suggested changes)
                    $originalQuantity =  $cartProduct['quantity'] ?? 1;

                    //  Set the available stock quantity
                    $stockQuantity = $relatedProduct->stock_quantity;

                    //  Check the no stock status
                    $noStock = ($relatedProduct->stock_quantity_type == 'limited') &&
                               ($stockQuantity == 0);

                    //  Check the limited stock status
                    $limitedStock = ($relatedProduct->stock_quantity_type == 'limited') &&
                                    ($stockQuantity < $originalQuantity) &&
                                    ($stockQuantity > 0);

                    //  If we have limited stock
                    if( $limitedStock ) {

                        //  Default to available stock quantity
                        $quantity = $stockQuantity;

                    //  If we have stock or we don't have stock
                    }else {

                        /**
                         *  (1) Has Stock
                         *  -------------
                         *
                         *  In this case we can default to the original quantity
                         *
                         *  (2) No stock
                         *  ------------
                         *
                         *  In this case we will default to the original quantity
                         *  rather than setting the value to Zero (0). This is
                         *  because we can have the original quantity so that
                         *  the pricing information is calculated but then
                         *  we set this product line as cancelled due to
                         *  no stock.
                         *
                         *  This way we can flexibly allow the store users
                         *  to uncancel this product line and process an
                         *  order with exactly what the customer wants.
                         *  This approach is more flexible.
                         */
                        $quantity = $originalQuantity;

                    }

                    //  Set the sub total (based on the unit regular price and quantity)
                    $subTotal = $relatedProduct->unit_regular_price * $quantity;

                    //  Set the sale discount (based on the sale discount and quantity)
                    $saleDiscountTotal = $relatedProduct->unit_sale_discount * $quantity;

                    //  Set the grand total (based on the unit price and quantity)
                    $grandTotal = $relatedProduct->unit_price * $quantity;

                    /**
                     *  Mock the Item Line Model from the related Product Model by collecting related
                     *  information that match the fillable fields of the Item Line Model.
                     *  Then merge additional related information.
                     */
                    return new ProductLine(
                        collect($relatedProduct->getAttributes())->merge([

                            //  Set pricing information (Totals)
                            'sale_discount_total' => $saleDiscountTotal,
                            'grand_total' => $grandTotal,
                            'sub_total' => $subTotal,

                            //  Set quantity information
                            'original_quantity' => $originalQuantity,
                            'quantity' => $quantity,

                            //  Set cancellation status information
                            'is_cancelled' => false,
                            'cancellation_reasons' => null,

                            //  Set detected changes information
                            'detected_changes' => [],

                            'location_id' => $this->location->id,
                            'product_id' => $relatedProduct->id

                        ])->toArray()
                    );

                }

            })->all();

        }

        //  Otherwise return nothing
        return [];
    }

    public function getSpecifiedCouponLines()
    {
        //  If we have atleast one location coupon
        if( $this->locationCoupons->count() ) {

            return $this->locationCoupons->map(function($locationCoupon) {

                $inValid = false;
                $isCancelled = false;
                $cancellationReasons = collect([]);

                //  Search for an existing coupon line that matches this location coupon
                $existingCouponLine = collect($this->existingCouponLines)->first(fn($existingCouponLine) => $existingCouponLine->coupon_id == $locationCoupon->id);

                //  If the coupon is not active then don't apply this coupon
                if( !$locationCoupon->active ) {

                    $inValid = true;
                    $cancellationReasons->push('Deactivated by store');

                };

                //  If the coupon activation depends on the coupon code
                if( $locationCoupon->activate_using_code ) {

                    //  If the coupon codes provided do not match the location code then don't apply this coupon
                    if( collect($this->cartCouponCodes)->doesntContain($locationCoupon->code) ) {


                        $inValid = true;
                        $cancellationReasons->push('Required a code for activation but the code provided was invalid');

                    }

                }

                //  If the coupon activation depends on the coupon minimum grand total
                if( $locationCoupon->activate_using_minimum_grand_total ) {

                    //  If the grand total is less than the minimum total then don't apply this coupon
                    if( $this->grandTotal < $locationCoupon->minimum_grand_total ) {

                        $inValid = true;

                        $minimumGrandTotal = $this->convertToCurrencyFormat($locationCoupon->minimum_grand_total, $this->store->currency);
                        $grandTotal = $this->convertToCurrencyFormat($this->grandTotal, $this->store->currency);

                        $cancellationReasons->push('Required a minimum grand total of '.$minimumGrandTotal.' but the cart total was valued at '.$grandTotal);

                    }

                }

                //  If the coupon activation depends on the coupon minimum products total
                if( $locationCoupon->activate_using_minimum_total_products ) {

                    //  If the uncancelled product line total is less than the minimum products total then don't apply this coupon
                    if( $this->totalSpecifiedUnCancelledProductLines < $locationCoupon->minimum_total_products ) {

                        $inValid = true;

                        $cancellationReasons->push(
                            ('Required a minimum total of '. $locationCoupon->minimum_total_products . ($locationCoupon->minimum_total_products == 1) ? ' unique item ' : ' unique items ') .
                            (', but the cart contained '.$this->totalSpecifiedUnCancelledProductLines . ($this->totalSpecifiedUnCancelledProductLines == 1) ? ' unique item ' : ' unique items ')
                        );

                    }

                }

                //  If the coupon activation depends on the coupon minimum total product quantities
                if( $locationCoupon->activate_using_minimum_total_product_quantities ) {

                    //  If the uncancelled product line quantities total is less than the minimum total product quantities then don't apply this coupon
                    if( $this->totalSpecifiedUncancelledProductLineQuantities < $locationCoupon->minimum_total_product_quantities ) {

                        $inValid = true;

                        $cancellationReasons->push(
                            ('Required a minimum total of '. $locationCoupon->minimum_total_product_quantities . ($locationCoupon->minimum_total_product_quantities == 1) ? ' total quantity ' : ' total quantities ') .
                            (', but the cart contained '.$this->totalSpecifiedUncancelledProductLineQuantities . ($this->totalSpecifiedUncancelledProductLineQuantities == 1) ? ' total quantity ' : ' total quantities ')
                        );

                    }

                }

                //  If the coupon activation depends on the coupon start datetime
                if( $locationCoupon->activate_using_start_datetime ) {

                    //  If the coupon start datetime is in the future then don't apply this coupon
                    if( \Carbon\Carbon::parse($locationCoupon->start_datetime)->isFuture() ) {

                        $inValid = true;

                        $cancellationReasons->push('Starting date was not yet reached');

                    }

                }

                //  If the coupon activation depends on the coupon end datetime
                if( $locationCoupon->activate_using_end_datetime ) {

                    //  If the coupon end datetime is in the past then don't apply this coupon
                    if( \Carbon\Carbon::parse($locationCoupon->end_datetime)->isPast() ) {

                        $inValid = true;

                        $cancellationReasons->push('Ending date was reached');

                    }

                }

                //  If the coupon activation depends on the coupon time (Specific hour of a 24hour day)
                if( $locationCoupon->activate_using_hours_of_day ) {

                    /**
                     *  If the current hour of the day is not present in the coupon
                     *  allowed hours of the day then don't apply this coupon
                     */
                    if( !in_array(Carbon::now()->format('H'), $locationCoupon->hours_of_day) ) {

                        $inValid = true;

                        $cancellationReasons->push('Invalid hour of the day (Activated at specific hours of the day)');

                    }

                }

                //  If the coupon activation depends on the coupon day of the week
                if( $locationCoupon->activate_using_days_of_the_week ) {

                    /**
                     *  If the current day of the week is not present in the coupon
                     *  allowed days of the week then don't apply this coupon
                     */
                    if( !in_array(Carbon::now()->format('l'), $locationCoupon->days_of_the_week) ) {

                        $inValid = true;

                        $cancellationReasons->push('Invalid day of the week (Activated on specific days of the week)');

                    }

                }

                //  If the coupon activation depends on the coupon day of the month
                if( $locationCoupon->activate_using_days_of_the_month ) {

                    /**
                     *  If the current day of the month is not present in the coupon
                     *  allowed days of the month then don't apply this coupon
                     */
                    if( !in_array(Carbon::now()->format('d'), $locationCoupon->days_of_the_month) ) {

                        $inValid = true;

                        $cancellationReasons->push('Invalid day of the month (Activated on specific days of the month)');

                    }

                }

                //  If the coupon activation depends on the coupon month of the year
                if( $locationCoupon->activate_using_months_of_the_year ) {

                    /**
                     *  If the current month of the year is not present in the coupon
                     *  allowed months of the year then don't apply this coupon
                     */
                    if( !in_array(Carbon::now()->format('F'), $locationCoupon->months_of_the_year) ) {

                        $inValid = true;

                        $cancellationReasons->push('Invalid month of the year (Activated on specific months of the year)');

                    }

                }

                //  If the coupon activation depends on the shopper as an new customer
                if( $locationCoupon->activate_for_new_customer ) {

                    //  If the current shopper is an existing customer then don't apply this coupon
                    if( $this->is_existing_customer == true ) {

                        $inValid = true;

                        $cancellationReasons->push('Must be a new customer');

                    }

                }

                //  If the coupon activation depends on the shopper as an existing customer
                if( $locationCoupon->activate_for_existing_customer ) {

                    //  If the current shopper is not an existing customer then don't apply this coupon
                    if( $this->is_existing_customer == false ) {

                        $inValid = true;

                        $cancellationReasons->push('Must be an existing customer');

                    }

                }

                //  If the coupon activation depends on the usage limit
                if( $locationCoupon->activate_using_usage_limit ) {

                    //  If the used quantity has reached or exceeded the limited quantity then don't apply this coupon
                    if( $locationCoupon->used_quantity >= $locationCoupon->limited_quantity ) {

                        $inValid = true;

                        $cancellationReasons->push('The usage limit was reached');

                    }

                }

                //  If the coupon is invalid and we don't have an existing coupon line
                if( $inValid == true && !$existingCouponLine ) {

                    //  Return null to exclude this coupon
                    return null;

                }

                /**
                 *  Mock the Coupon Line Model from the Location Coupon Model by collecting related
                 *  information that match the fillable fields of the Coupon Line Model. Then merge
                 *  additional related information.
                 *
                 *  @var Couponline $couponLine
                 */
                $couponLine = new Couponline(
                    collect($locationCoupon->getAttributes())->merge([

                        //  Set cancellation status information
                        'is_cancelled' => false,
                        'cancellation_reasons' => [],

                        //  Set detected changes information
                        'detected_changes' => [],

                        'location_id' => $this->location->id,
                        'coupon_id' => $locationCoupon->id

                    ])->toArray()
                );

                //  If we have an existing coupon line
                if( $existingCouponLine ) {

                    //  If this was not cancelled but is now invalid (We must cancel)
                    if($existingCouponLine->is_cancelled == false && $inValid == true) {

                        $message = 'The ('.$locationCoupon->name. ') coupon was removed because its no longer valid';
                        $couponLine->recordDetectedChange('cancelled', $message, $existingCouponLine)
                                   ->cancelItemLine($cancellationReasons);

                    //  If this was cancelled but is now valid (We must uncancel)
                    }elseif($existingCouponLine->is_cancelled == true && $inValid == false) {

                        $message = 'The ('.$locationCoupon->name. ') coupon was added because its valid again';
                        $couponLine->recordDetectedChange('uncancelled', $message, $existingCouponLine);

                    }

                }

                return $couponLine;

            })->filter()->all();

        }

        //  Otherwise return nothing
        return [];
    }

    /**
     *  Prepare the product lines for database entry
     *  @param int $cartId
     *  @param int|array<int> $productIds
     */
    public function prepareSpecifiedProductLinesForDB($cartId, $productIds = null, $convertToJson = true)
    {
        /**
         *  @param ProductLine $specifiedProductLine
         */
        $collection = collect($this->specifiedProductLines)->map(function($specifiedProductLine) use ($cartId, $productIds, $convertToJson) {

            //  Ready the product line for database insertion
            return $specifiedProductLine->readyForDatabase($cartId, $convertToJson);

        //  If the product ids specified as an integer or array of integers then we want to extract a specific entry
        })->when(is_array($productIds) || is_int($productIds), function ($specifiedProductLines, $value) use ($productIds) {

            /**
             *  If this is an integer then convert to an array containing the integer.
             *  Its important to know that the mutation of the $productIds does not
             *  change the value of the $productIds passed as a parameter to this
             *  method. We are mutating this value within the current scope only.
             */
            if( is_int($productIds) ) $productIds = [$productIds];

            //  Let us return only the specified product lines that match the given product ids
            return $specifiedProductLines->filter(fn($specifiedProductLine) => (collect($productIds)->contains($specifiedProductLine['product_id'])));

        //  If the product ids specified is a single integer then we want to extract a specific entry
        });

        //  If we expected to return a single result but found no results then return Null
        if( is_int($productIds) && $collection->count() === 0) return null;

        //  If we expected to return a single result, then return an associative array of the first entry
        if( is_int($productIds)) return $collection->first();

        //  Otherwise return the collection as an associative array
        return $collection->toArray();

    }

    /**
     *  Prepare the coupon lines for database entry
     *  @param int $cartId
     *  @param int|array<int> $couponIds
     */
    public function prepareSpecifiedCouponLinesForDB($cartId, $couponIds = null, $convertToJson = true)
    {
        /**
         *  @param CouponLine $specifiedCouponLine
         */
        $collection = collect($this->specifiedCouponLines)->map(function($specifiedCouponLine) use ($cartId, $couponIds, $convertToJson) {

            //  Ready the coupon line for database insertion
            return $specifiedCouponLine->readyForDatabase($cartId, $convertToJson);

        //  If the coupon ids specified as an integer or array of integers then we want to extract a specific entry
        })->when(is_array($couponIds) || is_int($couponIds), function ($specifiedCouponLines, $value) use ($couponIds) {

            /**
             *  If this is an integer then convert to an array containing the integer.
             *  Its important to know that the mutation of the $couponIds does not
             *  change the value of the $couponIds passed as a parameter to this
             *  method. We are mutating this value within the current scope only.
             */
            if( is_int($couponIds) ) $couponIds = [$couponIds];

            //  Let us return only the specified coupon lines that match the given coupon ids
            return $specifiedCouponLines->filter(fn($specifiedCouponLine) => (collect($couponIds)->contains($specifiedCouponLine['coupon_id'])));

        //  If the coupon ids specified is a single integer then we want to extract a specific entry
        });

        //  If we expected to return a single result but found no results then return Null
        if( is_int($couponIds) && $collection->count() === 0) return null;

        //  If we expected to return a single result, then return an associative array of the first entry
        if( is_int($couponIds)) return $collection->first();

        //  Otherwise return the collection as an associative array
        return $collection->toArray();

    }

    public function getSpecifiedCancelledProductLines()
    {
        return collect($this->specifiedProductLines)->filter(fn($productLine) => $productLine->is_cancelled)->all();
    }

    public function getSpecifiedUnCancelledProductLines()
    {
        return collect($this->specifiedProductLines)->filter(fn($productLine) => !$productLine->is_cancelled)->all();
    }

    public function countSpecifiedProductLines()
    {
        return collect($this->specifiedProductLines)->count();
    }

    public function countSpecifiedProductLineQuantities()
    {
        return collect($this->specifiedProductLines)->sum('quantity');
    }

    public function countSpecifiedCancelledProductLines()
    {
        return collect($this->getSpecifiedCancelledProductLines())->count();
    }

    public function countSpecifiedCancelledProductLineQuantities()
    {
        return collect($this->getSpecifiedCancelledProductLines())->sum('quantity');
    }

    public function countSpecifiedUnCancelledProductLines()
    {
        return collect($this->getSpecifiedUnCancelledProductLines())->count();
    }

    public function countSpecifiedUncancelledProductLineQuantities()
    {
        return collect($this->getSpecifiedUnCancelledProductLines())->sum('quantity');
    }

    public function calculateTotalCoupons()
    {
        return collect($this->specifiedCouponLines)->count();
    }

    public function calculateAndSetTotals()
    {
        /**
         *  Apply the totals from the uncancelled product lines collected
         */
        foreach($this->getSpecifiedUnCancelledProductLines() as $productLine){

            //  Calculate the total excluding sale discounts
            $this->subTotal += $productLine->sub_total;

            //  Calculate the total including sale discounts
            $this->grandTotal += $productLine->grand_total;

            //  Calculate the total sale discounts
            $this->saleDiscountTotal += $productLine->sale_discount_total;

        }

        //  Calculate the coupon discount total
        $this->couponDiscountTotal = $this->calculateCouponDiscount();

        //  Apply the coupon discount total
        $this->grandTotal -= $this->couponDiscountTotal;

        //  Calculate the sale and coupon discount total combined
        $this->couponAndSaleDiscountTotal = $this->saleDiscountTotal + $this->couponDiscountTotal;

        /**
         *  If we are not offering free delivery then apply the delivery fee
         *
         *  Note: The delivery fee is applied after the discounts have been
         *  applied to the grand total so that we can avoid discounting the
         *  delivery fee. The delivery fee must be applied as is without
         *  being discounted incase of percetage rate based discounts.
         */
        if( $this->allowFreeDelivery === false) {

            //  Calculate the delivery fee total
            $this->deliveryFee = $this->calculateDeliveryFee();

            //  Apply the delivery fee total
            $this->grandTotal += $this->deliveryFee;

        }
    }

    /**
     *  Calculate the total coupon discount
     */
    public function calculateCouponDiscount()
    {
        //  Collect coupons that offer discounts
        $couponsOfferingDiscounts = collect($this->specifiedCouponLines)->filter(fn($coupon) => $coupon->offer_discount);

        //  Sum the total of the discounts
        $totalCouponDiscount = $couponsOfferingDiscounts->map(function($coupon) {

            if( $coupon->discount_type == 'Percentage' ) {

                return ($coupon->discount_percentage_rate / 100) * $this->grandTotal;

            }elseif( $coupon->discount_type == 'Fixed' ) {

                return $coupon->discount_fixed_rate;

            }else {

                return 0;

            }

        })->sum();

        //  The total coupon discount cannot exceed the grand total
        return $totalCouponDiscount < $this->grandTotal ? $totalCouponDiscount : $this->grandTotal;
    }

    /**
     *  Calculate the delivery free
     */
    public function calculateDeliveryFee()
    {
        //  If the location supports delivery
        if( $this->location->allow_delivery ) {

            //  Return the matching destination delivery fee
            if( $deliveryDestination = $this->getDeliveryDestination() ) return $deliveryDestination['delivery_fee'];

            /**
             *  In the case that we could not match any specific delivery destination,
             *  then check if we generally charge a flat fee for any destination.
             */
            if( $this->location->delivery_flat_fee ) return $this->location->delivery_flat_fee;

        }

        //  Otherwise default to "0" as the delivery fee
        return 0;
    }

    /**
     *  Get the matching delivery destination
     */
    public function getDeliveryDestination()
    {
        //  If we provided a specific delivery destination name
        if( $this->deliveryDestinationName ) {

            //  Return the matching delivery destination
            return collect($this->location->delivery_destinations)->first(function($destination) {
                return $destination['name'] == $this->deliveryDestinationName;
            });

        }
    }

    /**
     *  Check if we can offer free delivery
     */
    public function offerFreeDelivery()
    {
        return $this->hasCouponToOfferFreeDelivery() || $this->hasDestinationToOfferFreeDelivery();
    }

    /**
     *  Check if we have a coupon that offers free delivery
     */
    public function hasCouponToOfferFreeDelivery()
    {
        return collect($this->specifiedCouponLines)->contains(fn($coupon) => $coupon->offer_free_delivery);
    }

    /**
     *  Check if we have coupons that offer free delivery
     */
    public function hasDestinationToOfferFreeDelivery()
    {
        //  If the location supports delivery
        if( $this->location->allow_delivery ) {

            //  Search the matching destination (Return whether this destination allows free delivery)
            if( $deliveryDestination = $this->getDeliveryDestination() ) return $deliveryDestination['allow_free_delivery'];

            /**
             *  In the case that we could not match any specific delivery destination,
             *  then check if we generally allow free delivery for any destination.
             */
            if( $this->location->allow_free_delivery ) return $this->location->allow_free_delivery;

        }

        //  Otherwise default to false that this location
        return false;
    }

    /**
     *  Detect changes that directly affect this product line such as
     *  changes on price, stock or availability.
     */
    public function detectChangesOnProductLines()
    {
        if( $this->specifiedProductLines ) {

            //  Foreach specified product line
            collect($this->specifiedProductLines)->each(function($specifiedProductLine) {

                /**
                 *  Get the related product of the specified product line
                 *  @var Product $relatedProduct
                 */
                $relatedProduct = collect($this->relatedProducts)->first(fn($relatedProduct) => $relatedProduct->id == $specifiedProductLine->product_id);

                /**
                 *  Get the existing product line of the specified product line
                 *  @var ProductLine $existingProductLine
                 */
                $existingProductLine = collect($this->existingProductLines)->first(fn($existingProductLine) => $existingProductLine->product_id == $specifiedProductLine->product_id);

                /**
                 *  There are two types of changes
                 *
                 *  (1) Changes that do not require comparisons with a database record of this product line
                 *      i.e We can compare the specified product line against the related product
                 *
                 *  (2) Changes that require comparisons with a database record of this product
                 *      i.e We can compare the specified product line against the existing
                 *      product line record stored in the database
                 *
                 *   We will handle these two changes in their respective order
                 */

                /**
                 *  If the specified product line does not have a matching existing product line
                 *  that is recorded in the database, then we can compare the specified product
                 *  line with the related product for now.
                 */
                $noStock = $relatedProduct->has_stock == false;
                $noPrice = $relatedProduct->has_price == false;
                $limitedStock = ($specifiedProductLine->quantity < $specifiedProductLine->original_quantity);

                //  If the related product does not have stock (Sold out)
                if( $noStock ) {

                    $noStockMessage = $specifiedProductLine->quantity.'x('.$relatedProduct->name.') removed because it sold out';
                    $specifiedProductLine->recordDetectedChange('no_stock', $noStockMessage, $existingProductLine)->cancelItemLine($noStockMessage);

                }

                //  If the specified product line has less quantities than intended (Limited Stock)
                if( $limitedStock ) {

                    $limitedStockMessage = $specifiedProductLine->original_quantity.'x('.$relatedProduct->name.') reduced to ('.$specifiedProductLine->quantity.') because of limited stock';
                    $specifiedProductLine->recordDetectedChange('limited_stock', $limitedStockMessage, $existingProductLine);

                }

                //  If the related product does not have a price
                if( $noPrice ) {

                    $noPriceMessage = $specifiedProductLine->quantity.'x('.$relatedProduct->name.') removed because it has no price';
                    $specifiedProductLine->recordDetectedChange('no_price', $noPriceMessage, $existingProductLine)->cancelItemLine($noPriceMessage);

                }

                /**
                 *  If the specified product line has a matching existing product line
                 *  that is recorded in the database, then we can compare changes on
                 *  the two states.
                 */
                if( $existingProductLine ) {

                    //  If the product line did not have stock but now we have enough stock
                    $noStockToEnoughStock = $existingProductLine->hasDetectedChange('no_stock') == true
                                            && $specifiedProductLine->hasDetectedChange('no_stock') == false
                                            && $specifiedProductLine->hasDetectedChange('limited_stock') == false;

                    //  If the product line did not have stock but now we have limited stock
                    $noStockToLimitedStock = $existingProductLine->hasDetectedChange('no_stock') == true
                                            && $specifiedProductLine->hasDetectedChange('limited_stock') == true;

                    //  If the product line had limited stock but now we have enough stock
                    $limitedStockToEnoughStock = $existingProductLine->hasDetectedChange('limited_stock') == true
                                                && $specifiedProductLine->hasDetectedChange('no_stock') == false
                                                && $specifiedProductLine->hasDetectedChange('limited_stock') == false;

                    if( $noStockToEnoughStock ) {

                        $noStockToEnoughStockMessage = $specifiedProductLine->quantity.'x('.$relatedProduct->name.') added because of new stock';
                        $specifiedProductLine->recordDetectedChange('no_stock_to_enough_stock', $noStockToEnoughStockMessage, $existingProductLine);

                    }elseif( $noStockToLimitedStock ) {

                        $noStockToLimitedStockMessage = 'Increased '.$relatedProduct->name.' quantity to '.$specifiedProductLine->quantity.' because of new stock';
                        $specifiedProductLine->recordDetectedChange('no_stock_to_limited_stock', $noStockToLimitedStockMessage, $existingProductLine);

                    }elseif( $limitedStockToEnoughStock ) {

                        $limitedStockToLimitedStockMessage = $specifiedProductLine->quantity.'x('.$relatedProduct->name.') added because of new stock';
                        $specifiedProductLine->recordDetectedChange('limited_stock_to_enough_stock', $limitedStockToLimitedStockMessage, $existingProductLine);

                    }

                    //  If the product line was free but is not free anymore
                    $freeToNotFree = $existingProductLine->is_free && !$specifiedProductLine->is_free;

                    //  If the product line was not free but is not free
                    $notFreeToFree = !$existingProductLine->is_free && $specifiedProductLine->is_free;

                    //  If the product line did not have a price but now has a new price
                    $noPriceToNewPrice = $existingProductLine->hasDetectedChange('no_price') == true
                                            && $specifiedProductLine->hasDetectedChange('no_price') == false;

                    //  If the product line did have a price but now the price changed
                    $oldPriceToNewPrice = $existingProductLine->unit_price != $specifiedProductLine->unit_price;

                    //  Get the existing product line unit price
                    $existingProductLineUnitPrice = $existingProductLine->convertToMoneyFormat($existingProductLine->unit_price)['currency_money'];

                    //  Get the specified product line unit price
                    $specifiedProductLineUnitPrice = $specifiedProductLine->convertToMoneyFormat($specifiedProductLine->unit_price)['currency_money'];

                    if( $freeToNotFree ){

                        $freeToNotFreeMessage = $specifiedProductLine->quantity.'x('.$relatedProduct->name.') added with new price '.$specifiedProductLineUnitPrice.' each';
                        $specifiedProductLine->recordDetectedChange('free_to_not_free', $freeToNotFreeMessage, $existingProductLine);

                    }elseif( $notFreeToFree ){

                        $notFreeToFreeMessage = $specifiedProductLine->quantity.'x('.$relatedProduct->name.') is now free';
                        $specifiedProductLine->recordDetectedChange('free_to_not_free', $notFreeToFreeMessage, $existingProductLine);

                    }elseif( $noPriceToNewPrice ) {

                        $noPriceToNewPriceMessage = $specifiedProductLine->quantity.'x('.$relatedProduct->name.') added with new price '.$specifiedProductLineUnitPrice.' each';
                        $specifiedProductLine->recordDetectedChange('no_price_to_new_price', $noPriceToNewPriceMessage, $existingProductLine);

                    }elseif( $oldPriceToNewPrice ) {

                        $inflation = $specifiedProductLine->unit_price > $existingProductLine->unit_price ? 'increased' : 'reduced';

                        $oldPriceToNewPriceMessage = $specifiedProductLine->quantity.'x('.$relatedProduct->name.') price '.$inflation.' from '.$existingProductLineUnitPrice .' to '.$specifiedProductLineUnitPrice.' each';

                        //  If the existing product line was not on sale but the sale started
                        if( !$existingProductLine->on_sale && $specifiedProductLine->on_sale ) {

                            $oldPriceToNewPriceMessage .= ' (On sale)';

                            if( $inflation == 'increased' ){

                                $changeType = 'old_price_to_new_price_increase_with_sale';

                            }else{

                                $changeType = 'old_price_to_new_price_decrease_with_sale';

                            }

                        //  If the existing product line was on sale but the sale ended
                        }elseif( $existingProductLine->on_sale && !$specifiedProductLine->on_sale ) {

                            $oldPriceToNewPriceMessage .= ' (Sale ended)';

                            if( $inflation == 'increased' ) {

                                $changeType = 'old_price_to_new_price_increase_without_sale';

                            }else{

                                $changeType = 'old_price_to_new_price_decrease_without_sale';

                            }

                        }else{

                            if( $inflation == 'increased' ) {

                                $changeType = 'old_price_to_new_price_increase';

                            }else{

                                $changeType = 'old_price_to_new_price_decrease';

                            }

                        }

                        $specifiedProductLine->recordDetectedChange($changeType, $oldPriceToNewPriceMessage, $existingProductLine);

                    }

                }

                //  Capture the detected changes to share with the shopping cart
                $this->detectedChanges = collect($this->detectedChanges)->merge($specifiedProductLine->detected_changes)->all();

            });

        }
    }

}
