<?php

use App\Enums\ProductUnit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            $table->string('image_path')->nullable();
            $table->string('unit', 20);
            $table->bigInteger('sale_price');
            $table->bigInteger('cost_price')->nullable();
            $table->unsignedInteger('low_stock_threshold')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_favorite')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'category_id']);
        });

        $units = implode(',', array_map(fn ($v) => "'{$v}'", ProductUnit::values()));
        DB::statement("ALTER TABLE products ADD CONSTRAINT chk_products_unit CHECK (unit IN ({$units}))");
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
