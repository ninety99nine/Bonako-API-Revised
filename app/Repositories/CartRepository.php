<?php

namespace App\Repositories;

use App\Exceptions\CartAlreadyConvertedException;
use App\Repositories\BaseRepository;
use App\Repositories\OrderRepository;
use App\Services\ShoppingCart\ShoppingCartService;

class CartRepository extends BaseRepository
{
    protected $requiresConfirmationBeforeDelete = true;

    /**
     *  Return the ShoppingCartService instance
     *
     *  @return ShoppingCartService
     */
    public function shoppingCartService()
    {
        return resolve(ShoppingCartService::class);
    }

    /**
     *  Return the OrderRepository instance
     *
     *  @return OrderRepository
     */
    public function orderRepository()
    {
        return resolve(OrderRepository::class);
    }

    /**
     *  Create the cart product lines and coupon lines for the cart.
     *  The creation of these resources strictly depends on
     *  the existence on the product lines otherwise we do
     *  not associate anything to the cart. This is
     *  important since we never want to have a
     *  cart that has a coupon applied without
     *  any product lines applied.
     */
    public function createProductAndCouponLines()
    {
        //  If we have specified product lines
        if( $this->shoppingCartService()->totalSpecifiedProductLines ) {

            return $this->createProductLines()->createCouponLines();

        }

        return $this;
    }

    /**
     *  Create the cart product lines for the cart
     */
    public function createProductLines($data = [])
    {
        $this->model->productLines()->insert(
            count($data) ? $data : $this->shoppingCartService()->prepareSpecifiedProductLinesForDB($this->model->id)
        );

        return $this;
    }

    /**
     *  Create the cart coupon lines for the cart
     */
    public function createCouponLines($data = [])
    {
        //  Then create the coupon lines
        $this->model->couponLines()->insert(
            count($data) ? $data : $this->shoppingCartService()->prepareSpecifiedCouponLinesForDB($this->model->id)
        );

        return $this;
    }

    /**
     *  Update the cart product lines and coupon lines for the cart.
     */
    public function updateProductAndCouponLines()
    {
        return $this->updateProductLines()->updateCouponLines();
    }

    /**
     *  Update the cart product lines for the cart
     */
    public function updateProductLines()
    {
        //  Get the existing product lines (Saved on the database)
        $existingProductLines = $this->shoppingCartService()->existingProductLines;

        $cancellationReason = 'Removed from the shopping cart';

        //  If we have specified product lines
        if( $this->shoppingCartService()->totalSpecifiedProductLines ) {

            //  Get the specified product lines (Not saved on the database)
            $specifiedProductLines = $this->shoppingCartService()->specifiedProductLines;

            /**
             *  Split the specified product lines as either existing or new product lines.
             *
             *  The [new] represent those that have already been saved to the database
             *  before. They may or may not have any changes applied such as change in
             *  quantities or cancellation status. These product lines must be updated.
             *
             *  The [existing] represent those that have not been saved to the database
             *  at all. These are new product lines that must be created for the first
             *  time.
             */
            [$existingSpecifiedProductLines, $newSpecifiedProductLines] = collect($specifiedProductLines)->partition(function($specifiedProductLine) use ($existingProductLines) {

                return collect( collect($existingProductLines)->pluck('product_id') )->contains($specifiedProductLine->product_id);

            });

            //  If we have existing specified product lines
            if( $existingSpecifiedProductLines->count() ) {

                //  Foreach existing product line
                collect($existingProductLines)->each(function($existingProductLine) use ($cancellationReason) {

                    //  Get the existing specified product line product id
                    $productId = $existingProductLine->product_id;

                    //  Get the specified product line database entry information (one entry)
                    $data = $this->shoppingCartService()->prepareSpecifiedProductLinesForDB($this->model->id, $productId, false);

                    /**
                     *  If the data returned is null, then this means that the existing product line
                     *  does not match with any of the specified product lines. In this case we must
                     *  delete this existing product line since it is no longer present in the cart.
                     */
                    if( $data === null ) {

                        /**
                         *  We could delete this product line saved in the database as follows:
                         *
                         *  $existingProductLine->delete();
                         *
                         *  However this approach does not give anyone any idea of the existence of
                         *  this product line. So instead we could just cancel it with a message
                         *  that the item was removed from the cart
                         */

                        //  Delete the existing product line from the database
                        $existingProductLine->clearDetectedChanges()->clearCancellationReasons()->cancelItemLine($cancellationReason)->save();

                    }else{

                        //  Lets update it using the information from the existing specified product line
                        $existingProductLine->update($data);

                    }

                });

            }

            //  If we have new specified product lines
            if( $newSpecifiedProductLines->count() ) {

                //  Get the new specified product line product ids
                $productIds = $newSpecifiedProductLines->pluck('product_id')->toArray();

                //  Get the specified product lines database entries information (array of multiple entries)
                $data = $this->shoppingCartService()->prepareSpecifiedProductLinesForDB($this->model->id, $productIds);

                //  Lets create them using the information from the existing specified product lines
                $this->createProductLines($data);

            }

        //  Otherwise this means that the product lines have been removed
        }else{

            /**
             *  We could delete these product lines saved in the database as follows:
             *
             *  $this->model->productLines()->delete();
             *
             *  However this approach does not give anyone any idea of the existence of
             *  these product lines. So instead we could just cancel it with a message
             *  that the item was removed from the cart
             */
            collect($existingProductLines)->each(function($existingProductLine) use ($cancellationReason) {
                $existingProductLine->clearDetectedChanges()->clearCancellationReasons()->cancelItemLine($cancellationReason)->save();
            });

        }

        return $this;
    }

    /**
     *  Update the cart coupon lines for the cart
     */
    public function updateCouponLines()
    {
        //  Get the existing coupon lines (Saved on the database)
        $existingCouponLines = $this->shoppingCartService()->existingCouponLines;

        $cancellationReason = 'Removed from the shopping cart';

        //  If we have specified coupon lines
        if( $this->shoppingCartService()->totalSpecifiedCouponLines ) {

            //  Get the specified coupon lines (Not saved on the database)
            $specifiedCouponLines = $this->shoppingCartService()->specifiedCouponLines;

            /**
             *  Split the specified coupon lines as either existing or new coupon lines.
             *
             *  The [new] represent those that have already been saved to the database
             *  before. They may or may not have any changes applied such as change in
             *  quantities or cancellation status. These coupon lines must be updated.
             *
             *  The [existing] represent those that have not been saved to the database
             *  at all. These are new coupon lines that must be created for the first
             *  time.
             */
            [$existingSpecifiedCouponLines, $newSpecifiedCouponLines] = collect($specifiedCouponLines)->partition(function($specifiedCouponLine) use ($existingCouponLines) {

                return collect( collect($existingCouponLines)->pluck('coupon_id') )->contains($specifiedCouponLine->coupon_id);

            });

            //  If we have existing specified coupon lines
            if( $existingSpecifiedCouponLines->count() ) {

                //  Foreach existing coupon line
                collect($existingCouponLines)->each(function($existingCouponLine) use ($cancellationReason) {

                    //  Get the existing specified coupon line coupon id
                    $couponId = $existingCouponLine->coupon_id;

                    //  Get the specified coupon line database entry information (one entry)
                    $data = $this->shoppingCartService()->prepareSpecifiedCouponLinesForDB($this->model->id, $couponId, false);

                    /**
                     *  If the data returned is null, then this means that the existing coupon line
                     *  does not match with any of the specified coupon lines. In this case we must
                     *  delete this existing coupon line since it is no longer present in the cart.
                     */
                    if( $data === null ) {

                        /**
                         *  We could delete this coupon line saved in the database as follows:
                         *
                         *  $existingCouponLine->delete();
                         *
                         *  However this approach does not give anyone any idea of the existence of
                         *  this coupon line. So instead we could just cancel it with a message
                         *  that the item was removed from the cart
                         */

                        //  Delete the existing coupon line from the database
                        $existingCouponLine->clearDetectedChanges()->clearCancellationReasons()->cancelItemLine($cancellationReason)->save();

                    }else{

                        //  Lets update it using the information from the existing specified coupon line
                        $existingCouponLine->update($data);

                    }

                });

            }

            //  If we have new specified coupon lines
            if( $newSpecifiedCouponLines->count() ) {

                //  Get the new specified coupon line coupon ids
                $couponIds = $newSpecifiedCouponLines->pluck('coupon_id')->toArray();

                //  Get the specified coupon lines database entries information (array of multiple entries)
                $data = $this->shoppingCartService()->prepareSpecifiedCouponLinesForDB($this->model->id, $couponIds);

                //  Lets create them using the information from the existing specified coupon lines
                $this->createCouponLines($data);

            }

        //  Otherwise this means that the coupon lines have been removed
        }else{

            /**
             *  We could delete these coupon lines saved in the database as follows:
             *
             *  $this->model->couponLines()->delete();
             *
             *  However this approach does not give anyone any idea of the existence of
             *  these coupon lines. So instead we could just cancel it with a message
             *  that the item was removed from the cart
             */
            collect($existingCouponLines)->each(function($existingCouponLine) use ($cancellationReason) {
                $existingCouponLine->clearDetectedChanges()->clearCancellationReasons()->cancelItemLine($cancellationReason)->save();
            });

        }

        return $this;
    }

    /**
     *  Empty the cart
     */
    public function empty()
    {
        //  Delete the cart product lines (permanently)
        $this->model->productLines()->delete();

        //  Delete the cart coupon lines (permanently)
        $this->model->couponLines()->delete();

        //  Empty the current shopping cart
        $emptyshoppingCart = $this->shoppingCartService()->emptyCart()->startInspection();

        //  Update the saved shopping cart to the current empty shopping cart
        $this->update($emptyshoppingCart);

        //  Return the cart repository instance
        return $this;
    }

    /**
     *  Convert the cart to an order
     */
    public function convert()
    {
        //  Check if the cart has been converted before
        if( $this->checkIfConverted() ) throw new CartAlreadyConvertedException;

        //  Get the Cart Model instance
        $cart = $this->model;

        //  Create a new order
        $orderRepository = $this->orderRepository()->create([
            'customer_id' => $cart->owner_id,
            'location_id' => $cart->location_id
        ]);

        //  If we have a new order
        if( $order = $orderRepository->model ) {

            //  Associate the cart with the new order
            $this->update([
                'owner_id' => $order->id,
                'owner_type' => $order->getResourceType()
            ]);

        }

        //  Return the cart repository instance
        return $this;
    }

    public function checkIfConverted()
    {
        return $this->model->getResourceType() == 'cart';
    }
}
