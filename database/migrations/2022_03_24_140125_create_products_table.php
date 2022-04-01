<?php

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {

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
            $table->boolean('visible')->default(true);
            $table->boolean('show_description')->default(false);
            $table->string('description', 500)->nullable();

            /*  Tracking Information  */
            $table->string('sku', 100)->nullable()->index();
            $table->string('barcode', 100)->nullable()->index();

            /*  Variation Information  */
            $table->boolean('allow_variations')->default(false);
            $table->json('variant_attributes')->nullable();

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

            /*  Quantity Information  */
            $table->enum('allowed_quantity_per_order', Product::ALLOWED_QUANTITY_PER_ORDER)->default(Arr::last(Product::ALLOWED_QUANTITY_PER_ORDER));
            $table->unsignedSmallInteger('maximum_allowed_quantity_per_order')->default(2);

            /*  Stock Information  */
            $table->boolean('has_stock')->default(false);
            $table->enum('stock_quantity_type', Product::STOCK_QUANTITY_TYPE)->default(Arr::last(Product::STOCK_QUANTITY_TYPE));
            $table->unsignedMediumInteger('stock_quantity')->default(0);

            /*  Arrangement Information  */
            $table->unsignedTinyInteger('arrangement')->nullable();

            /*  Ownership Information  */
            $table->foreignId('parent_product_id')->nullable();
            $table->foreignId('location_id');
            $table->foreignId('user_id');

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
        Schema::dropIfExists('products');
    }
}
