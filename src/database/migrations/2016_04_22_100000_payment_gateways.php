<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PaymentGateways extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_gateways',function(Blueprint $table){
            $table->increments('id');
            $table->boolean('active')   ->default(0);
            $table->string('name');
            $table->tinyInteger('type');
            $table->boolean('test')     ->default(0);
            $table->string('config')    ->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('payment_modules');
    }
}