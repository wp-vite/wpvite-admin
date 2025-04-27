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
            $table->ulid('site_id')->primary();
            $table->foreignUlid('user_id')->constrained();
            $table->foreignUlid('server_id')->constrained('hosting_servers', 'server_id');
            $table->foreignUlid('template_id')->constrained('templates', 'template_id');

            $table->unsignedTinyInteger('status')->default(10); // Setup Pending
            $table->unsignedTinyInteger('setup_progress')->nullable();

            $table->string('domain')->nullable()->unique();
            $table->string('dns_provider', 30)->nullable();
            $table->string('dns_record_id', 50)->nullable()->unique();
            $table->string('root_directory')->nullable();
            $table->string('site_owner_username', 20)->nullable();
            $table->json('auth_data');
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
