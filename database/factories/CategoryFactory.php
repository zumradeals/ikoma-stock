<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->randomElement(['Fer & Métallurgie', 'Bois & Menuiserie', 'Ciment & Matériaux', 'Quincaillerie', 'Peinture & Finition']),
            'description' => fake()->sentence(),
            'image_path' => null,
            'display_order' => fake()->numberBetween(0, 10),
        ];
    }
}
