<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBestchangeRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bestchange_rates', function (Blueprint $table) {
            $table->id();
            $table->integer('curr1');
            $table->integer('curr2');
            $table->decimal('rate',16,8);
            $table->decimal('diff',6,3);
            $table->decimal('rub_rate',16,8);
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
        Schema::dropIfExists('bestchange_rates');
    }
}
