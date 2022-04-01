<?php

use App\Models\Coupon;
use Illuminate\Support\Arr;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {

            $table->increments('id');

            /*  General Information */
            $table->string('name', 50)->nullable()->index();
            $table->string('description', 500)->nullable();
            $table->boolean('active')->default(true);

            /*  Offer Discount Information */
            $table->boolean('offer_discount')->default(false);
            $table->enum('discount_type', Coupon::DISCOUNT_TYPES)->default(Arr::last(Coupon::DISCOUNT_TYPES));
            $table->unsignedTinyInteger('discount_percentage_rate')->default(0);
            $table->decimal('discount_fixed_rate', 9, 2)->default(0);

            /*  Offer Free Delivery Information */
            $table->boolean('offer_free_delivery')->default(false);

            /*  Activation Information  */
            $table->boolean('activate_using_code')->default(false);
            $table->string('code', 10)->nullable();

            $table->boolean('activate_using_minimum_grand_total')->default(false);
            $table->decimal('minimum_grand_total', 9, 2)->default(0);
            $table->char('currency', 3)->default('BWP');

            $table->boolean('activate_using_minimum_total_products')->default(false);
            $table->unsignedSmallInteger('minimum_total_products')->default(1);

            $table->boolean('activate_using_minimum_total_product_quantities')->default(false);
            $table->unsignedSmallInteger('minimum_total_product_quantities')->default(1);

            $table->boolean('activate_using_start_datetime')->default(false);
            $table->timestamp('start_datetime')->nullable();

            $table->boolean('activate_using_end_datetime')->default(false);
            $table->timestamp('end_datetime')->nullable();

            $table->boolean('activate_using_hours_of_day')->default(false);
            $table->json('hours_of_day')->nullable();

            $table->boolean('activate_using_days_of_the_week')->default(false);
            $table->json('days_of_the_week')->nullable();

            $table->boolean('activate_using_days_of_the_month')->default(false);
            $table->json('days_of_the_month')->nullable();

            $table->boolean('activate_using_months_of_the_year')->default(false);
            $table->json('months_of_the_year')->nullable();

            $table->boolean('activate_for_new_customer')->default(false);
            $table->boolean('activate_for_existing_customer')->default(false);

            $table->boolean('activate_using_usage_limit')->default(false);
            $table->unsignedSmallInteger('limited_quantity')->default(1);
            $table->unsignedSmallInteger('used_quantity')->default(0);

            /*  Ownership  */
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
        Schema::dropIfExists('coupons');
    }
}
