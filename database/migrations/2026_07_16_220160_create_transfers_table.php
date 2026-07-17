<?php

use App\Enums\TransferStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('number');
            $table->foreignId('source_warehouse_id')->nullable()->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('source_outlet_id')->nullable()->constrained('outlets')->restrictOnDelete();
            $table->foreignId('destination_warehouse_id')->nullable()->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('destination_outlet_id')->nullable()->constrained('outlets')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 20)->default('DRAFT');
            $table->bigInteger('total_quantity')->default(0);
            $table->bigInteger('shipped_quantity')->default(0);
            $table->bigInteger('received_quantity')->default(0);
            $table->timestamp('request_date')->nullable();
            $table->timestamp('ship_date')->nullable();
            $table->timestamp('receive_date')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'number']);
        });

        $statuses = implode(',', array_map(fn ($v) => "'{$v}'", TransferStatus::values()));
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE transfers ADD CONSTRAINT chk_transfers_status CHECK (status IN ({$statuses}))"); }
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
