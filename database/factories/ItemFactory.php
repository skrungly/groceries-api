<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * generate a random percentage based on an Item's product quantity.
     *
     * for product->quantity = 1, the percent values are 0 < % < 100 (continuous).
     * for product->quantity > 1, they are instead multiples of 100% (discrete).
     *
     * it is biased (kinda arbitrarily) towards empty and full percentages.
     *
     * @param array $attr an array of current Item attributes
     *
     * @return int a percentage of the overall product quantity
     */
    private static function randQuantityPercent(array $attr): int
    {
        $quantity = Product::find($attr['product_id'])->quantity;

        return collect([
            0,
            $quantity * 100,
            $quantity == 1 ? rand(0, 100) : rand(0, $quantity) * 100,
        ])->random();
    }

    /**
     * define the Item's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'expires_at' => collect([null, now()->endOfDay()->subSecond()->addDays(rand(-3, 7))])->random(),
            'opened_at' => collect([null, now()->subHours(rand(0, 48))])->random(),
            'percent_remaining' => self::randQuantityPercent(...),

            // if the item has 0% remaining, choose either zero or random waste
            'percent_wasted' => fn ($attr) => $attr['percent_remaining'] ? 0 : self::randQuantityPercent($attr),
        ];
    }
}
