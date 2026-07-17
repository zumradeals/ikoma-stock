<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Compteur technique interne (pas une entité métier du brief) utilisé par
     * App\Services\DocumentNumberGenerator pour générer des numéros
     * PREFIX-YYYYMM-NNNN sans collision, y compris pour le tout premier
     * document d'une période (une simple requête MAX(number)+lockForUpdate
     * ne verrouille rien s'il n'existe encore aucune ligne à verrouiller).
     */
    public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('document_type', 20);
            $table->string('period', 6);
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'document_type', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
