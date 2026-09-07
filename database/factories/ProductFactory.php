<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * define the Product's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'barcode' => fake()->ean13(),
            'shelf_life_opened' => collect([null, rand(1, 7)])->random(),
            'quantity' => collect([1, rand(1, 8)])->random(),
            'cost' => collect([null, rand(100, 500) / 100])->random(),
            'net_weight' => collect([null, rand(50, 500)])->random(),
        ];
    }
}
