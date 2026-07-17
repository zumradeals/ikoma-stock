<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pas de company_id ici : l'isolation tenant passe par le Transfer parent.
     */
    public function up(): void
    {
        Schema::create('transfer_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('transfers')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->bigInteger('requested_quantity');
            $table->bigInteger('shipped_quantity')->default(0);
            $table->bigInteger('received_quantity')->default(0);
            $table->timestamps();
        });

        if (DB::getDriverName() !== 'sqlite') { DB::statement('ALTER TABLE transfer_lines ADD CONSTRAINT chk_transfer_lines_quantity CHECK (requested_quantity > 0)'); }
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_lines');
    }
};
