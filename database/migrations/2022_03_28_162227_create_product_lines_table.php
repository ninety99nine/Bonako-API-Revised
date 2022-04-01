<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_lines', function (Blueprint $table) {

            $table->increments('id');

            /*  General Information
             *
             *  Note: The product name can be up to 60 characters
             *  since variation names can have long generated
             *  names e.g
             *
             *  Nike Winter Jacket (Red, Large and Cotton) = 42 characters
             *
             *  Lets give an allowance of 60 characters to avoid
             *  possible issues because of long product names
             */
            $table->string('name', 60)->nullable()->index();
            $table->string('description', 500)->nullable();

            /*  Tracking Information  */
            $table->string('sku', 100)->nullable()->index();
            $table->string('barcode', 100)->nullable()->index();

            /*  Pricing Information  */
            $table->boolean('is_free')->default(false);
            $table->char('currency', 3)->default('BWP');
            $table->decimal('unit_regular_price', 9, 2)->default(0);

            $table->boolean('on_sale')->default(false);
            $table->decimal('unit_sale_price', 9, 2)->default(0);
            $table->decimal('unit_sale_discount', 9, 2)->default(0);
            $table->unsignedSmallInteger('unit_sale_discount_percentage')->default(0);

            $table->boolean('has_price')->default(false);
            $table->decimal('unit_price', 9, 2)->default(0);
            $table->decimal('unit_cost', 9, 2)->default(0);

            $table->decimal('unit_profit', 9, 2)->default(0);
            $table->unsignedSmallInteger('unit_profit_percentage')->default(0);

            $table->decimal('unit_loss', 9, 2)->default(0);
            $table->unsignedSmallInteger('unit_loss_percentage')->default(0);

            $table->decimal('sale_discount_total', 9, 2)->default(0);
            $table->decimal('grand_total', 9, 2)->default(0);
            $table->decimal('sub_total', 9, 2)->default(0);

            /*  Quantity Information  */
            $table->unsignedSmallInteger('original_quantity')->default(1);
            $table->unsignedSmallInteger('quantity')->default(1);

            /*  Cancellation Information  */
            $table->boolean('is_cancelled')->default(false);
            $table->json('cancellation_reasons')->nullable();

            /*  Detected Changes Information  */
            $table->json('detected_changes')->nullable();

            /*  Ownership Information  */
            $table->foreignId('location_id');
            $table->foreignId('product_id');
            $table->foreignId('cart_id');

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
        Schema::dropIfExists('product_lines');
    }
}
