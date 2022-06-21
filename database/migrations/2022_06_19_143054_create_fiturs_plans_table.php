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
        Schema::create(config('subpay.tables.fiturs') . '_' . config('subpay.tables.plans'), function (Blueprint $table) {
            $table->foreignId('plan_id')->constrained('plans');
            $table->foreignId('fitur_id')->constrained('fiturs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('subpay.tables.fiturs') . '_' . config('subpay.tables.plans'));
    }
};
