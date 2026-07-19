<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->string('app_name')->nullable()->after('mail_from_name');
            $table->string('app_tagline')->nullable()->after('app_name');
            $table->string('app_logo_path')->nullable()->after('app_tagline');
        });
    }

    public function down(): void
    {
        Schema::table('platform_settings', function (Blueprint $table) {
            $table->dropColumn(['app_name', 'app_tagline', 'app_logo_path']);
        });
    }
};
