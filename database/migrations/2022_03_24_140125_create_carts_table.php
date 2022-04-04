<?php

use App\Models\Cart;
use Illuminate\Support\Arr;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {

            $table->increments('id');

            /*  Pricing  */
            $table->char('currency', 3)->default('BWP');
            $table->decimal('sub_total', 9, 2)->default(0);
            $table->decimal('coupon_discount_total', 9, 2)->default(0);
            $table->decimal('sale_discount_total', 9, 2)->default(0);
            $table->decimal('coupon_and_sale_discount_total', 9, 2)->default(0);
            $table->decimal('grand_total', 9, 2)->default(0);

            /*  Delivery  */
            $table->boolean('allow_free_delivery')->default(false);
            $table->decimal('delivery_fee', 9, 2)->default(0);
            $table->json('delivery_destination')->nullable();

            /*  Product Line Totals  */
            $table->unsignedSmallInteger('total_products')->default(0);
            $table->unsignedSmallInteger('total_product_quantities')->default(0);
            $table->unsignedSmallInteger('total_cancelled_products')->default(0);
            $table->unsignedSmallInteger('total_cancelled_product_quantities')->default(0);
            $table->unsignedSmallInteger('total_uncancelled_products')->default(0);
            $table->unsignedSmallInteger('total_uncancelled_product_quantities')->default(0);

            /*  Coupon Line Totals  */
            $table->unsignedSmallInteger('total_coupons')->default(0);

            /*  Changes  */
            $table->json('products_arrangement')->nullable();
            $table->json('detected_changes')->nullable();
            $table->boolean('abandoned_status')->default(false);

            /*  Instant Cart  */
            $table->foreignId('instant_cart_id')->nullable();

            /*  Ownership  */
            $table->foreignId('location_id');
            $table->foreignId('owner_id');
            $table->string('owner_type');

            /*  Timestamps  */
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carts');
    }
}
