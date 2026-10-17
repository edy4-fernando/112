<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('patient_orders')) {
            Schema::table('patient_orders', function (Blueprint $table): void {
                if (! Schema::hasColumn('patient_orders', 'payment_method')) {
                    $table->string('payment_method')->nullable();
                }

                if (! Schema::hasColumn('patient_orders', 'status')) {
                    $table->enum('status', ['Pendiente', 'Completado', 'Cancelado'])->default('Pendiente');
                }
            });
        }

        if (Schema::hasTable('patient_order_items') && ! Schema::hasColumn('patient_order_items', 'product_name')) {
            Schema::table('patient_order_items', function (Blueprint $table): void {
                $table->string('product_name')->nullable();
            });

            DB::table('patient_order_items')
                ->join('catalog_items', 'patient_order_items.catalog_item_id', '=', 'catalog_items.id')
                ->update(['patient_order_items.product_name' => DB::raw('catalog_items.name')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('patient_order_items') && Schema::hasColumn('patient_order_items', 'product_name')) {
            Schema::table('patient_order_items', function (Blueprint $table): void {
                $table->dropColumn('product_name');
            });
        }

        if (Schema::hasTable('patient_orders')) {
            $columns = array_filter(
                ['payment_method', 'status'],
                fn (string $column): bool => Schema::hasColumn('patient_orders', $column),
            );

            if ($columns !== []) {
                Schema::table('patient_orders', function (Blueprint $table) use ($columns): void {
                    $table->dropColumn($columns);
                });
            }
        }
    }
};
