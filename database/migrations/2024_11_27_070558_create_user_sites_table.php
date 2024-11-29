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
        Schema::create('user_sites', function (Blueprint $table) {
            $table->id('site_id');
            $table->string('site_uid', 20)->unique();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('template_id')->constrained('templates', 'template_id');
            $table->foreignId('server_id')->constrained('hosting_servers', 'server_id');
            $table->string('domain')->unique();
            $table->unsignedTinyInteger('status')->default(1); // 0 => Inactive, 1 => Active, 3 => Suspended
            $table->timestamps();
            $table->softDeletes()->index();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sites');
    }
};
