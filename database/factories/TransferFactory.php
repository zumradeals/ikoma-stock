<?php

namespace Database\Factories;

use App\Enums\TransferStatus;
use App\Models\Company;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transfer>
 */
class TransferFactory extends Factory
{
    protected $model = Transfer::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'number' => 'TRF-'.now()->format('Ym').'-'.fake()->unique()->numerify('####'),
            'source_warehouse_id' => Warehouse::factory(),
            'source_outlet_id' => null,
            'destination_warehouse_id' => null,
            'destination_outlet_id' => null,
            'user_id' => User::factory(),
            'status' => TransferStatus::DRAFT,
            'total_quantity' => 0,
            'shipped_quantity' => 0,
            'received_quantity' => 0,
            'request_date' => now(),
            'ship_date' => null,
            'receive_date' => null,
            'note' => null,
        ];
    }
}
