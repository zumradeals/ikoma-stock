<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'action' => 'created',
            'entity_type' => 'App\\Models\\Product',
            'entity_id' => fake()->numberBetween(1, 1000),
            'old_values' => null,
            'new_values' => null,
            'device_info' => fake()->userAgent(),
            'ip_address' => fake()->ipv4(),
            'session_id' => fake()->uuid(),
        ];
    }
}
