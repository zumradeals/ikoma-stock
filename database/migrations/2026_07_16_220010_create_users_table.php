<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * outlet_id est volontairement sans contrainte FK ici (la table
     * `outlets` n'existe pas encore — dépendance circulaire users<->outlets).
     * La FK est ajoutée dans add_outlet_foreign_to_users_table.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->restrictOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role', 30);
            $table->unsignedBigInteger('outlet_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['company_id', 'email']);
            $table->index('outlet_id');
        });

        $roles = implode(',', array_map(fn ($v) => "'{$v}'", UserRole::values()));
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE users ADD CONSTRAINT chk_users_role CHECK (role IN ({$roles}))"); }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
