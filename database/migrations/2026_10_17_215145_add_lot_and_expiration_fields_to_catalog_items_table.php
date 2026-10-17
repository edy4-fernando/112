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
        if (! Schema::hasTable('catalog_items')) {
            return;
        }

        Schema::table('catalog_items', function (Blueprint $table) {
            if (! Schema::hasColumn('catalog_items', 'lot_number')) {
                $table->string('lot_number')->nullable();
            }

            if (! Schema::hasColumn('catalog_items', 'manufactured_at')) {
                $table->date('manufactured_at')->nullable();
            }

            if (! Schema::hasColumn('catalog_items', 'expires_at')) {
                $table->date('expires_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('catalog_items')) {
            return;
        }

        $columns = array_values(array_filter(
            ['lot_number', 'manufactured_at', 'expires_at'],
            fn (string $column): bool => Schema::hasColumn('catalog_items', $column),
        ));

        if ($columns !== []) {
            Schema::table('catalog_items', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
