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
            $table->ulid('template_id')->primary();
            $table->string('title', 100);
            $table->text('description')->nullable();

            $table->foreignId('category_id')->constrained('template_categories', 'category_id');
            $table->foreignUlid('server_id')->constrained('hosting_servers', 'server_id');

            $table->unsignedTinyInteger('status')->default(10)->index(); // Setup Pending
            $table->unsignedTinyInteger('setup_progress')->nullable();

            $table->string('domain')->nullable()->unique();
            $table->string('dns_provider', 30)->nullable();
            $table->string('dns_record_id', 50)->nullable()->unique();
            $table->string('root_directory')->nullable();
            $table->string('site_owner_username', 20)->nullable();
            $table->json('auth_data')->nullable();

            $table->timestamp('published_at')->nullable()->index();
            $table->float('current_version', 2)->nullable();

            $table->timestamps();
            $table->softDeletes();
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
