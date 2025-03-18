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
            $table->text('description')->nullable();

            $table->unsignedTinyInteger('status')->default(10); // Setup Pending
            $table->unsignedTinyInteger('setup_progress')->nullable()->default(null);

            $table->foreignId('category_id')->constrained('template_categories', 'category_id');
            $table->foreignId('server_id')->constrained('hosting_servers', 'server_id');
            $table->string('domain')->nullable()->default(null)->unique();
            $table->string('dns_provider', 30)->nullable()->default(null);
            $table->string('dns_record_id', 50)->unique()->nullable()->default(null);
            $table->string('root_directory')->nullable()->default(null);
            $table->string('site_owner_username', 20)->nullable()->default(null);
            $table->json('auth_data');
            $table->timestamps();
            $table->softDeletes();
+
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
