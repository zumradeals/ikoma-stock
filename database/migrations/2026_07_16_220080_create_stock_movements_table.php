<?php

use App\Enums\LocationType;
use App\Enums\StockMovementType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Journal append-only : uniquement created_at, jamais d'updated_at
     * (un mouvement de stock ne se corrige pas, il se contre-passe via un
     * nouveau mouvement INVENTORY_CORRECTION).
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('movement_type', 30);
            $table->bigInteger('quantity');
            $table->string('location_source_type', 20)->nullable();
            $table->unsignedBigInteger('location_source_id')->nullable();
            $table->string('location_destination_type', 20)->nullable();
            $table->unsignedBigInteger('location_destination_id')->nullable();
            $table->string('reason')->nullable();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('movement_date');
            $table->string('document_type')->nullable();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['company_id', 'product_id', 'movement_date']);
        });

        $movementTypes = implode(',', array_map(fn ($v) => "'{$v}'", StockMovementType::values()));
        DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT chk_stock_movements_type CHECK (movement_type IN ({$movementTypes}))");

        $locationTypes = implode(',', array_map(fn ($v) => "'{$v}'", LocationType::values()));
        DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT chk_stock_movements_src_type CHECK (location_source_type IS NULL OR location_source_type IN ({$locationTypes}))");
        DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT chk_stock_movements_dst_type CHECK (location_destination_type IS NULL OR location_destination_type IN ({$locationTypes}))");

        DB::statement('ALTER TABLE stock_movements ADD CONSTRAINT chk_stock_movements_quantity CHECK (quantity > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
