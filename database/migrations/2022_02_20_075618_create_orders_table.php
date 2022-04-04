<?php

use App\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {

            $table->id();

            /*  Status Information  */
            $table->enum('payment_status', Order::PAYMENT_STATUSES)->default(Arr::last(Order::PAYMENT_STATUSES));
            $table->enum('delivery_status', Order::DELIVERY_STATUSES)->default(Arr::last(Order::DELIVERY_STATUSES));

            /*  Cancellation Information  */
            $table->boolean('is_cancelled')->default(false);
            $table->string('cancellation_reason')->nullable();

            /*  Delivery Information  */
            $table->boolean('delivery_verified')->default(false);
            $table->timestamp('delivery_verified_at')->nullable();
            $table->char('delivery_confirmation_code', 6)->nullable();
            $table->foreignId('delivery_verified_by_user_id')->nullable();

            /*  Customer Information  */
            $table->foreignId('customer_id');

            /*  Customer Information  */
            $table->foreignId('location_id');

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
        Schema::dropIfExists('orders');
    }
}
