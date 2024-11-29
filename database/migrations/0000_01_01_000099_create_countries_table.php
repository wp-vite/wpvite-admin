<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id('country_id');
            $table->string('name', 100);
            $table->char('iso_code', 2)->unique(); // ISO Alpha-2 code (e.g., US, IN)
            $table->unsignedSmallInteger('isd_code'); // ISD code (e.g., 1, 91)
            $table->char('currency_code', 3); // ISO Currency code (e.g., USD, INR)
            $table->char('currency_symbol', 10); // Currency symbol (e.g., $, ₹)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
