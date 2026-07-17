<?php

use App\Enums\CustomerType;
use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('number');
            $table->foreignId('outlet_id')->constrained('outlets')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->restrictOnDelete();
            $table->string('customer_type', 20);
            $table->bigInteger('total_amount')->default(0);
            $table->bigInteger('discount_amount')->default(0);
            $table->unsignedInteger('discount_percentage')->default(0);
            $table->string('payment_method_primary', 30)->nullable();
            $table->string('status', 20)->default('DRAFT');
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'number']);
        });

        $customerTypes = implode(',', array_map(fn ($v) => "'{$v}'", CustomerType::values()));
        DB::statement("ALTER TABLE sales ADD CONSTRAINT chk_sales_customer_type CHECK (customer_type IN ({$customerTypes}))");

        $statuses = implode(',', array_map(fn ($v) => "'{$v}'", SaleStatus::values()));
        DB::statement("ALTER TABLE sales ADD CONSTRAINT chk_sales_status CHECK (status IN ({$statuses}))");

        $paymentMethods = implode(',', array_map(fn ($v) => "'{$v}'", PaymentMethod::values()));
        DB::statement("ALTER TABLE sales ADD CONSTRAINT chk_sales_payment_method CHECK (payment_method_primary IS NULL OR payment_method_primary IN ({$paymentMethods}))");
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
