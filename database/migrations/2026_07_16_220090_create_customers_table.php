<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('neighborhood_city')->nullable();
            $table->string('tax_id')->nullable();
            $table->bigInteger('credit_limit')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->bigInteger('total_purchased')->default(0);
            $table->bigInteger('outstanding_balance')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
