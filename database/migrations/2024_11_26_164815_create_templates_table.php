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
        Schema::create('templates', function (Blueprint $table) {
            $table->id('template_id');
            $table->string('template_uid', 20)->unique();
            $table->string('title', 100);
            $table->text('description');
            $table->foreignId('category_id')->constrained('template_categories', 'category_id');
            $table->unsignedTinyInteger('status'); // 1 => Active, 2 => Inactive
            $table->foreignId('server_id')->constrained('hosting_servers', 'server_id');
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
