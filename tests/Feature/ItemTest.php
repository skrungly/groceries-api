<?php
use App\Models\Item;
use App\Models\Product;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

pest()->use(RefreshDatabase::class);

test('items can be created', function () {
    $product = Product::factory()
        ->hasItems(10)
        ->create();

    $item = Item::factory()
        ->for($product)
        ->make([
            "opened_at" => Carbon::now()->toISOString(),
            "expires_at" => Carbon::now()->endOfDay()->toISOString(),
        ]);

    $response = $this->postJson("/api/items", $item->toArray());

    $response->assertStatus(201);
    $response->assertJson(fn (AssertableJson $result) =>
        $result->whereAll($item->toArray())
            ->hasAll(['id', 'updated_at', 'created_at'])
            ->etc()
    );
});
