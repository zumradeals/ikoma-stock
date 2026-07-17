<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permet à DailyClosingService::addPayment() de rattacher un paiement
     * au point de journée qui l'a encaissé. Nullable : un paiement peut
     * être enregistré hors de tout point de journée ouvert.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('daily_closing_id')->nullable()->after('invoice_id')
                ->constrained('daily_closings')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['daily_closing_id']);
            $table->dropColumn('daily_closing_id');
        });
    }
};
