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
        Schema::create('template_previews', function (Blueprint $table) {
            $table->id('preview_id')->primary();
            $table->foreignUlid('template_id')->constrained('templates', 'template_id')->cascadeOnDelete();
            $table->string('title', 50); // e.g. "Home", "About", etc.
            $table->string('device', 20)->default('desktop'); // optional: desktop/mobile/tablet
            $table->string('image_filename', 100)->nullable();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('position')->default(99); // optional: for ordering
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_previews');
    }
};
