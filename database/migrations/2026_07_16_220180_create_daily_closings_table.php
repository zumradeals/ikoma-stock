<?php

use App\Enums\DailyClosingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('outlet_id')->constrained('outlets')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('business_date');
            $table->bigInteger('total_sales')->default(0);
            $table->bigInteger('cash_sales')->default(0);
            $table->bigInteger('mobile_money_sales')->default(0);
            $table->bigInteger('transfer_sales')->default(0);
            $table->bigInteger('credit_sales')->default(0);
            $table->bigInteger('collected_old_receivables')->default(0);
            $table->bigInteger('total_discounts')->default(0);
            $table->unsignedInteger('cancelled_invoices_count')->default(0);
            $table->unsignedInteger('delivered_products_count')->default(0);
            $table->bigInteger('declared_cash_amount')->nullable();
            $table->bigInteger('cash_difference')->default(0);
            $table->text('observations')->nullable();
            $table->string('status', 20)->default('OPEN');
            $table->foreignId('validated_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });

        $statuses = implode(',', array_map(fn ($v) => "'{$v}'", DailyClosingStatus::values()));
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE daily_closings ADD CONSTRAINT chk_daily_closings_status CHECK (status IN ({$statuses}))"); }
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closings');
    }
};
