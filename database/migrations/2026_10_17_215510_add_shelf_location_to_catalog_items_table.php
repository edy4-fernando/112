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
        if (! Schema::hasTable('catalog_items') || Schema::hasColumn('catalog_items', 'shelf_location')) {
            return;
        }

        Schema::table('catalog_items', fn (Blueprint $table) => $table->string('shelf_location')->nullable());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('catalog_items') && Schema::hasColumn('catalog_items', 'shelf_location')) {
            Schema::table('catalog_items', fn (Blueprint $table) => $table->dropColumn('shelf_location'));
        }
    }
};
