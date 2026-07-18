<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Déduplique les numéros non-nuls avant d'ajouter la contrainte.
        // On garde l'enregistrement le plus ancien (id MIN) et on remet les
        // autres à NULL — phone est nullable, donc cela ne casse rien.
        if (DB::getDriverName() === 'sqlite') {
            DB::statement(
                "UPDATE users SET phone = NULL
                 WHERE phone IS NOT NULL
                   AND id NOT IN (
                       SELECT MIN(id) FROM users
                       WHERE phone IS NOT NULL
                       GROUP BY phone
                   )"
            );
        } else {
            DB::statement(
                "UPDATE users u
                 INNER JOIN (
                     SELECT MIN(id) AS keep_id, phone
                     FROM users
                     WHERE phone IS NOT NULL
                     GROUP BY phone
                     HAVING COUNT(*) > 1
                 ) dup ON u.phone = dup.phone AND u.id != dup.keep_id
                 SET u.phone = NULL"
            );
        }

        Schema::table('users', function (Blueprint $table) {
            // NULL est autorisé plusieurs fois (comportement SQL standard)
            $table->unique('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
        });
    }
};
