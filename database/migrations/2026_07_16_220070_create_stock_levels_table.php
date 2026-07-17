<?php

use App\Enums\LocationType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('location_type', 20);
            $table->unsignedBigInteger('location_id');
            $table->bigInteger('quantity_physical')->default(0);
            $table->bigInteger('quantity_reserved')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'location_type', 'location_id']);
            $table->index(['company_id', 'location_type', 'location_id']);
        });

        $types = implode(',', array_map(fn ($v) => "'{$v}'", LocationType::values()));
        DB::statement("ALTER TABLE stock_levels ADD CONSTRAINT chk_stock_levels_location_type CHECK (location_type IN ({$types}))");
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_levels');
    }
};
