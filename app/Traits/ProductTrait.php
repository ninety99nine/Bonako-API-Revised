<?php

namespace App\Traits;

use App\Traits\Base\BaseTrait;

trait ProductTrait
{
    use BaseTrait;

    /**
     *  Calculate the unit price based on whether this product
     *  is Free, on sale or listed with its regular price.
     */
    public function calculateUnitPrice()
    {
        if( $this->is_free ) return 0;

        return $this->on_sale ? $this->unit_sale_price : $this->unit_regular_price;
    }

    /**
     *  Determine if this product is on sale
     */
    public function determineIfOnSale()
    {
        return !$this->is_free
               && ($this->unit_sale_price != 0)
               && ($this->unit_regular_price != 0)
               && ($this->unit_sale_price < $this->unit_regular_price);
    }

    /**
     *  Calculate the unit sale discount
     */
    public function calculateUnitSaleDiscount()
    {
        if( !$this->on_sale || $this->unit_regular_price == 0 ) return 0;

        return ($difference = ($this->unit_regular_price - $this->unit_sale_price)) >= 0 ? $difference : 0;
    }

    /**
     *  Calculate the unit sale percentage
     */
    public function calculateUnitSaleDiscountPercentage()
    {
        if( ($unitSaleDiscount = $this->calculateUnitSaleDiscount()) == 0 ) return 0;

        $percentage = ($unitSaleDiscount / $this->unit_regular_price) * 100;

        return round($percentage);
    }

    /**
     *  Calculate the unit profit
     */
    public function calculateUnitProfit()
    {
        $unitPrice = $this->calculateUnitPrice();
        return ($difference = ($unitPrice - $this->unit_cost)) >= 0 ? $difference : 0;
    }

    /**
     *  Calculate the unit percentage profit
     */
    public function calculateUnitProfitPercentage()
    {
        //  If it costs us nothing then we make a full profit
        if( $this->unit_cost == 0 ) return 100;

        $unitProfit = $this->calculateUnitProfit();

        $percentage = ($unitProfit / $this->unit_cost) * 100;

        return round($percentage);
    }

    /**
     *  Calculate the unit loss
     */
    public function calculateUnitLoss()
    {
        $unitPrice = $this->calculateUnitPrice();
        return ($difference = ($unitPrice - $this->unit_cost)) < 0 ? -$difference : 0;
    }

    /**
     *  Calculate the unit percentage loss
     */
    public function calculateUnitLossPercentage()
    {
        //  If it costs us nothing then we cannot make a loss
        if( $this->unit_cost == 0 ) return 0;

        $unitLoss = $this->calculateUnitLoss();

        $percentage = ($unitLoss / $this->unit_cost) * 100;

        return round($percentage);
    }

    /**
     *  Determine if this product has a price
     */
    public function determineIfHasPrice()
    {
        $unitPrice = $this->calculateUnitPrice();
        return !$this->is_free && $unitPrice > 0;
    }

    /**
     *  Determine if this product has stock
     */
    public function determineIfHasStock()
    {
        /**
         *  When creating a new product, its possible that the "stock_quantity_type" and
         *  "stock_quantity" attributes may not be provided. In this case attempting to
         *  access the "stock_quantity_type" and "stock_quantity" will return Null but
         *  upon creation they will attain their default values from the database table
         *  schema. Since we set the "stock_quantity_type" to "unlimited" on our Schema
         *  as the default value, this then means that a new product will always have
         *  unlimited stock. So we can assume that we have stock even if the value
         *  of "stock_quantity_type" is Null for now.
         */
        return is_null($this->stock_quantity_type) ||
               ($this->stock_quantity_type == 'unlimited') ||
               ($this->stock_quantity_type == 'limited' && $this->stock_quantity > 0);
    }

}
