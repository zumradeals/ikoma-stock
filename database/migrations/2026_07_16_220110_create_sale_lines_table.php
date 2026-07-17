<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pas de company_id ici : l'isolation tenant passe par le Sale parent
     * (voir Sale::saleLines()).
     */
    public function up(): void
    {
        Schema::create('sale_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->bigInteger('quantity');
            $table->bigInteger('unit_price');
            $table->bigInteger('line_discount')->default(0);
            $table->bigInteger('line_total');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE sale_lines ADD CONSTRAINT chk_sale_lines_quantity CHECK (quantity > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_lines');
    }
};
