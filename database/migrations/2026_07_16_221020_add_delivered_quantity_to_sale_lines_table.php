<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cumul de la quantité déjà livrée pour cette ligne de vente, mis à jour
     * par DeliveryService::deliver(). Évite de resommer delivery_lines à
     * chaque contrôle DeliveryExceedsOrderedQuantityException.
     */
    public function up(): void
    {
        Schema::table('sale_lines', function (Blueprint $table) {
            $table->bigInteger('delivered_quantity')->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('sale_lines', function (Blueprint $table) {
            $table->dropColumn('delivered_quantity');
        });
    }
};
