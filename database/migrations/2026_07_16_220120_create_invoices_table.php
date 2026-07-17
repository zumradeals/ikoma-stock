<?php

use App\Enums\InvoiceDeliveryStatus;
use App\Enums\InvoicePaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('sale_id')->constrained('sales')->restrictOnDelete();
            $table->string('number');
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->bigInteger('total_amount')->default(0);
            $table->bigInteger('paid_amount')->default(0);
            $table->bigInteger('balance_due')->default(0);
            $table->string('payment_status', 20)->default('UNPAID');
            $table->string('delivery_status', 20)->default('TO_PREPARE');
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'number']);
            $table->index(['company_id', 'payment_status']);
            $table->index(['company_id', 'delivery_status']);
            $table->index(['company_id', 'issue_date']);
        });

        DB::statement('ALTER TABLE invoices ADD CONSTRAINT chk_invoices_balance_due CHECK (balance_due >= 0)');
        DB::statement('ALTER TABLE invoices ADD CONSTRAINT chk_invoices_paid_amount CHECK (paid_amount <= total_amount)');

        $paymentStatuses = implode(',', array_map(fn ($v) => "'{$v}'", InvoicePaymentStatus::values()));
        DB::statement("ALTER TABLE invoices ADD CONSTRAINT chk_invoices_payment_status CHECK (payment_status IN ({$paymentStatuses}))");

        $deliveryStatuses = implode(',', array_map(fn ($v) => "'{$v}'", InvoiceDeliveryStatus::values()));
        DB::statement("ALTER TABLE invoices ADD CONSTRAINT chk_invoices_delivery_status CHECK (delivery_status IN ({$deliveryStatuses}))");
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
