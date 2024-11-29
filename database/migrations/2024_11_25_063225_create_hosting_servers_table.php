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
        Schema::create('hosting_servers', function (Blueprint $table) {
            $table->id('server_id');
            $table->string('name', 50);
            $table->string('provider', 20);
            $table->string('instance_type', 30);
            $table->string('public_ip', 45);
            $table->string('private_ip', 45)->nullable();
            $table->string('instance_id', 100)->nullable();
            $table->string('virtualmin_url', 255);
            $table->unsignedTinyInteger('max_sites');
            $table->unsignedTinyInteger('cpu');
            $table->unsignedTinyInteger('ram');
            $table->unsignedSmallInteger('disk_size');
            $table->unsignedTinyInteger('status')->default(2); // 0 => Inactive, 1 => Active, 2 => Maintenance
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosting_servers');
    }
};
