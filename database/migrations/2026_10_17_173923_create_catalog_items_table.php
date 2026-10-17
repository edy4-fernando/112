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
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->id();
            $table->string('brand')->default('Carmelitas');      // Carmelitas o Inkahampi[cite: 25, 37]
            $table->string('name');                               // Ej: Gastrizan, Chanca Piedra[cite: 39, 50]
            $table->string('health_category');                    // Ej: Digestiva, Óseo/Articular[cite: 38, 39]
            $table->string('presentation_type');                  // Bebida Concentrada, Cápsulas, Harina[cite: 27, 50, 57]
            $table->string('weight_volume');                      // 700ml, 1L, 100 caps, 500g[cite: 27, 37, 50, 58]
            $table->decimal('price_public', 8, 2);
            $table->decimal('price_dealer', 8, 2)->nullable();
            $table->integer('stock')->default(50);
            $table->string('image')->nullable();                  // Nombre del archivo en public/images/products/
            $table->text('benefits')->nullable();
            $table->text('usage_instructions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_items');
    }
};
