<?php

use App\Models\Store;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20);
            $table->string('call_to_action', 20);
            $table->enum('registered_with_bank', Store::CLOSED_ANSWERS)->default(Arr::last(Store::CLOSED_ANSWERS));
            $table->enum('banking_with', Store::BANKING_WITH)->default(Arr::last(Store::BANKING_WITH));
            $table->enum('registered_with_cipa', Store::CLOSED_ANSWERS)->default(Arr::last(Store::CLOSED_ANSWERS));
            $table->enum('registered_with_cipa_as', Store::REGISTERED_WITH_CIPA_AS)->default(Arr::last(Store::REGISTERED_WITH_CIPA_AS));
            $table->string('company_uin', 13)->nullable();
            $table->unsignedSmallInteger('number_of_employees')->default(0);
            $table->string('accepted_golden_rules');
            $table->foreignId('user_id')->default(0);
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
        Schema::dropIfExists('stores');
    }
}
