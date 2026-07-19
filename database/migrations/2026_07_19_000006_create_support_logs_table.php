<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('super_admin_id')->constrained('users');
            $table->foreignId('impersonated_user_id')->constrained('users');
            $table->foreignId('company_id')->constrained('companies');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_logs');
    }
};
