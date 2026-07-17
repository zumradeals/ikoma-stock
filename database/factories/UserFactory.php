<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Database\Factories\Concerns\GeneratesIvorianData;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    use GeneratesIvorianData;

    protected static ?string $password;

    protected $model = User::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => $this->ivorianPhone(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::SELLER,
            'outlet_id' => null,
            'is_active' => true,
            'last_login_at' => null,
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => [
            'company_id' => null,
            'role' => UserRole::SUPER_ADMIN,
            'outlet_id' => null,
        ]);
    }

    public function role(UserRole $role): static
    {
        return $this->state(fn () => ['role' => $role]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
