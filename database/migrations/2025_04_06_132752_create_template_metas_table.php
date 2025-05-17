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
        Schema::create('template_metas', function (Blueprint $table) {
            $table->ulid('meta_id')->primary();
            $table->foreignUlid('template_id')->constrained('templates', 'template_id');
            $table->string('meta_key', 50)->index();
            $table->string('meta_value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_metas');
    }
};
