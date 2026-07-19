<?php

use App\Enums\SubscriptionPlan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('subscription_plan')
                ->default(SubscriptionPlan::DECOUVERTE->value)
                ->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('subscription_plan');
        });
    }
};
