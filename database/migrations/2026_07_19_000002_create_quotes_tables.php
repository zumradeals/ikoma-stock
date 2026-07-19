<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('number');
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_type')->default('REGISTERED');
            $table->date('valid_until')->nullable();
            $table->bigInteger('total_amount')->default(0);
            $table->bigInteger('discount_amount')->default(0);
            $table->unsignedInteger('discount_percentage')->default(0);
            $table->string('status')->default('DRAFT');
            $table->foreignId('converted_sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'number']);
        });

        Schema::create('quote_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('quantity');
            $table->bigInteger('unit_price');
            $table->bigInteger('line_discount')->default(0);
            $table->bigInteger('line_total');
            $table->timestamps();
        });

        // Promote quotes module to available now that the feature is built
        DB::table('modules')->where('code', 'quotes')->update(['status' => 'available']);
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_lines');
        Schema::dropIfExists('quotes');
        DB::table('modules')->where('code', 'quotes')->update(['status' => 'planned']);
    }
};
