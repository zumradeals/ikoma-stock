<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pas de company_id : l'isolation tenant passe par le Delivery parent
     * (même pattern que SaleLine/TransferLine).
     */
    public function up(): void
    {
        Schema::create('delivery_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->restrictOnDelete();
            $table->foreignId('sale_line_id')->constrained('sale_lines')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->bigInteger('quantity_delivered');
            $table->timestamp('created_at')->useCurrent();
        });

        DB::statement('ALTER TABLE delivery_lines ADD CONSTRAINT chk_delivery_lines_quantity CHECK (quantity_delivered > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_lines');
    }
};
