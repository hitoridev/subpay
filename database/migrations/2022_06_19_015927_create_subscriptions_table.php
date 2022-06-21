<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('subpay.tables.subscriptions'), function (Blueprint $table) {
            $table->id();
            $table->morphs('subscriber');
            $table->integer('plan_id')->unsigned();
            $table->json('name');
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on(config('subpay.tables.plans'))->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('subpay.tables.subscriptions'));
    }
};
