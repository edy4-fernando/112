<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'dni')) {
                $table->string('dni')->nullable()->after('name');
            }
            if (! Schema::hasColumn('customers', 'age')) {
                $table->integer('age')->nullable()->after('dni');
            }
            if (! Schema::hasColumn('customers', 'visit_count')) {
                $table->integer('visit_count')->default(0)->after('last_purchase_date');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        $columns = ['dni', 'age', 'visit_count'];
        $existingColumns = array_filter($columns, fn (string $column): bool => Schema::hasColumn('customers', $column));

        if ($existingColumns !== []) {
            Schema::table('customers', function (Blueprint $table) use ($existingColumns): void {
                $table->dropColumn($existingColumns);
            });
        }
    }
};
