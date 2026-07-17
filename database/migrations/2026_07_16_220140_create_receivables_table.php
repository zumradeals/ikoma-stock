<?php

use App\Enums\ReceivableStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receivables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->restrictOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->bigInteger('initial_amount');
            $table->bigInteger('total_paid')->default(0);
            $table->bigInteger('balance_due');
            $table->date('due_date')->nullable();
            $table->unsignedInteger('days_overdue')->default(0);
            $table->timestamp('last_reminder_at')->nullable();
            $table->timestamp('next_reminder_at')->nullable();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('status', 20)->default('OPEN');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE receivables ADD CONSTRAINT chk_receivables_balance_due CHECK (balance_due >= 0)');

        $statuses = implode(',', array_map(fn ($v) => "'{$v}'", ReceivableStatus::values()));
        DB::statement("ALTER TABLE receivables ADD CONSTRAINT chk_receivables_status CHECK (status IN ({$statuses}))");
    }

    public function down(): void
    {
        Schema::dropIfExists('receivables');
    }
};
