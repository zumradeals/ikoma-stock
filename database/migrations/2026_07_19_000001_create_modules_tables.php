<?php

use App\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['available', 'planned', 'deprecated'])->default('available');
            $table->enum('pricing_type', ['free', 'paid'])->nullable();
            $table->unsignedInteger('price')->nullable()->comment('en centimes');
            $table->timestamps();
        });

        Schema::create('company_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->timestamp('enabled_at')->nullable();
            $table->foreignId('enabled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unique(['company_id', 'module_id']);
            $table->timestamps();
        });

        // Seed catalogue
        $now = now();

        DB::table('modules')->insert([
            [
                'code'         => 'deliveries',
                'name'         => 'Livraisons',
                'description'  => 'Suivi des livraisons clients',
                'status'       => 'available',
                'pricing_type' => 'free',
                'price'        => null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'code'         => 'quotes',
                'name'         => 'Devis',
                'description'  => 'Création et suivi des devis',
                'status'       => 'planned',
                'pricing_type' => null,
                'price'        => null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'code'         => 'qr_verification',
                'name'         => 'Vérification QR',
                'description'  => 'Authentification des produits par QR code',
                'status'       => 'planned',
                'pricing_type' => null,
                'price'        => null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ]);

        // Activate deliveries for all existing companies
        $deliveriesId = DB::table('modules')->where('code', 'deliveries')->value('id');

        $companyIds = DB::table('companies')->pluck('id');

        $rows = $companyIds->map(fn ($id) => [
            'company_id' => $id,
            'module_id'  => $deliveriesId,
            'enabled'    => true,
            'enabled_at' => $now,
            'enabled_by' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if ($rows) {
            DB::table('company_modules')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_modules');
        Schema::dropIfExists('modules');
    }
};
