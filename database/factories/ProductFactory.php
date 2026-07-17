<?php

namespace Database\Factories;

use App\Enums\ProductUnit;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Produits réalistes fer/bois/ciment/quincaillerie avec leur unité et
     * une fourchette de prix FCFA plausible (avant conversion en centimes).
     */
    private const CATALOG = [
        ['name' => 'Ciment CIMAF 50kg', 'unit' => ProductUnit::BAG, 'price' => [4500, 5500]],
        ['name' => 'Fer à béton 8mm', 'unit' => ProductUnit::BAR, 'price' => [2500, 3200]],
        ['name' => 'Fer à béton 12mm', 'unit' => ProductUnit::BAR, 'price' => [5800, 7200]],
        ['name' => 'Tôle bac acier 2m', 'unit' => ProductUnit::SHEET, 'price' => [7500, 9500]],
        ['name' => 'Planche de coffrage', 'unit' => ProductUnit::PLANK, 'price' => [1500, 2200]],
        ['name' => 'Chevron bois 6m', 'unit' => ProductUnit::UNIT, 'price' => [3500, 4800]],
        ['name' => 'Sable de construction', 'unit' => ProductUnit::M3, 'price' => [12000, 16000]],
        ['name' => 'Gravier concassé', 'unit' => ProductUnit::M3, 'price' => [14000, 18000]],
        ['name' => 'Clous 10cm (boîte)', 'unit' => ProductUnit::PACK, 'price' => [1200, 1800]],
        ['name' => 'Peinture façade 20L', 'unit' => ProductUnit::UNIT, 'price' => [22000, 28000]],
        ['name' => 'Tuyau PVC 100mm', 'unit' => ProductUnit::METER, 'price' => [2800, 3600]],
        ['name' => 'Grillage soudé', 'unit' => ProductUnit::METER, 'price' => [1800, 2400]],
    ];

    public function definition(): array
    {
        $item = fake()->randomElement(self::CATALOG);
        $salePrice = fake()->numberBetween(...$item['price']);
        $costPrice = (int) round($salePrice * fake()->randomFloat(2, 0.65, 0.85));

        return [
            'company_id' => Company::factory(),
            'category_id' => Category::factory(),
            'name' => $item['name'],
            'description' => null,
            'reference' => strtoupper(fake()->bothify('REF-####')),
            'image_path' => null,
            'unit' => $item['unit'],
            'sale_price' => $salePrice * 100,
            'cost_price' => $costPrice * 100,
            'low_stock_threshold' => fake()->numberBetween(5, 50),
            'is_active' => true,
            'is_favorite' => fake()->boolean(20),
            'display_order' => fake()->numberBetween(0, 20),
        ];
    }
}
