<?php

use Illuminate\Support\Arr;
use App\Models\MobileVerification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMobileVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mobile_verifications', function (Blueprint $table) {
            $table->id();
            $table->char('code', 6)->nullable();
            $table->string('mobile_number', 11)->index();
            $table->enum('purpose', MobileVerification::PURPOSE)->default(Arr::first(MobileVerification::PURPOSE));
            $table->timestamps();

            $table->index(['mobile_number', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mobile_verifications');
    }
}
