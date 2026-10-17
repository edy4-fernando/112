<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Campos adicionales para extender tu tabla 'customers' existente (Tratamientos de 3 Meses y Segmentación)
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'segment')) {
                $table->enum('segment', ['Adulto Mayor', 'Regular', 'Distribuidor'])->default('Regular')->after('phone');
            }
            if (! Schema::hasColumn('customers', 'treatment_start_date')) {
                $table->date('treatment_start_date')->nullable();
            }
            if (! Schema::hasColumn('customers', 'treatment_end_date')) {
                $table->date('treatment_end_date')->nullable();
            }
            if (! Schema::hasColumn('customers', 'treatment_status')) {
                $table->enum('treatment_status', ['En Curso', 'Completado', 'Pausado', 'Sin Tratamiento'])->default('Sin Tratamiento');
            }
            if (! Schema::hasColumn('customers', 'health_notes')) {
                $table->text('health_notes')->nullable();
            }
        });

        // Tabla de Materia Prima e Insumos (Control de Producción de Harinas, Cápsulas, Bebidas)
        if (! Schema::hasTable('raw_materials')) {
            Schema::create('raw_materials', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // ej: Quinua, Kiwicha, Frascos, Cápsulas vacías
                $table->decimal('stock_qty', 10, 2);
                $table->string('unit'); // Kg, Litros, Unidades
                $table->decimal('min_stock_alert', 10, 2)->default(10.00);
                $table->decimal('cost_per_unit', 10, 2);
                $table->timestamps();
            });
        }

        // Historial Detallado de Órdenes vinculadas a Clientes/Pacientes
        if (! Schema::hasTable('patient_orders')) {
            Schema::create('patient_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
                $table->decimal('total_amount', 10, 2);
                $table->enum('channel', ['Tienda Física', 'WhatsApp / Web'])->default('WhatsApp / Web');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('patient_order_items')) {
            Schema::create('patient_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_order_id')->constrained('patient_orders')->onDelete('cascade');
                $table->foreignId('catalog_item_id')->constrained('catalog_items')->onDelete('cascade');
                $table->integer('quantity');
                $table->decimal('unit_price', 10, 2);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_order_items');
        Schema::dropIfExists('patient_orders');
        Schema::dropIfExists('raw_materials');

        if (Schema::hasTable('customers')) {
            $columns = ['segment', 'treatment_start_date', 'treatment_end_date', 'treatment_status', 'health_notes'];
            $existingColumns = array_filter($columns, fn (string $column): bool => Schema::hasColumn('customers', $column));

            if ($existingColumns !== []) {
                Schema::table('customers', function (Blueprint $table) use ($existingColumns): void {
                    $table->dropColumn($existingColumns);
                });
            }
        }
    }
};
