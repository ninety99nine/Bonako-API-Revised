<?php

use App\Models\Location;
use Illuminate\Support\Arr;
use App\Models\Pivots\LocationUser;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('location_id');
            $table->boolean('default_location')->default(false);
            $table->enum('accepted_invitation', LocationUser::CLOSED_ANSWERS)->default(Arr::last(LocationUser::CLOSED_ANSWERS));
            $table->enum('role', Location::ROLES)->default(Arr::last(Location::ROLES));
            $table->json('permissions')->nullable();
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
        Schema::dropIfExists('location_user');
    }
}
