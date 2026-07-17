<?php

use App\Enums\PaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Journal append-only : un paiement enregistré ne se modifie pas, une
     * correction se fait via un nouvel enregistrement.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->restrictOnDelete();
            $table->bigInteger('amount');
            $table->string('method', 20);
            $table->date('payment_date');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('reference')->nullable();
            $table->string('proof_path')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        $methods = implode(',', array_map(fn ($v) => "'{$v}'", PaymentMethod::values()));
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE payments ADD CONSTRAINT chk_payments_method CHECK (method IN ({$methods}))"); }
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
