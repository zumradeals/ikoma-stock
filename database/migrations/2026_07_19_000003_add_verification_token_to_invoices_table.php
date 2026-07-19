<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('verification_token', 64)->nullable()->unique()->after('pdf_path');
        });

        // Backfill existing invoices
        \App\Models\Invoice::whereNull('verification_token')->each(function ($invoice) {
            $invoice->updateQuietly(['verification_token' => Str::random(32)]);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('verification_token');
        });
    }
};
