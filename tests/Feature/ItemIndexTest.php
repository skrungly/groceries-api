<?php
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;

const ITEM_ATTRIBUTES = [
    // db columns
    'id',
    'user_id',
    'product_id',
    'updated_at',
    'created_at',
    'expires_at',
    'opened_at',
    'percent_remaining',
    'percent_wasted',

    // additional attributes
    'product',
    'soonest_expiry',
];

pest()->use(RefreshDatabase::class);

test('items index is initially empty', function () {
    $response = $this->getJson('/api/items');

    $response->assertStatus(200);
    $response->assertExactJson([]);
});

test('items index provides list of items', function () {
    Product::factory()
        ->count(3)
        ->hasItems(10)
        ->create();

    $response = $this->getJson('/api/items');

    $response->assertStatus(200);

    $response->assertJson(fn (AssertableJson $itemList) =>
        $itemList->has(30)->first(fn (AssertableJson $item) =>
            $item->hasAll(ITEM_ATTRIBUTES)
        )
    );
});

test('items index can be filtered by barcode', function () {
    $products = Product::factory()
        ->count(3)
        ->hasItems(10)
        ->create();

    $barcode = $products->first()->barcode;

    $response = $this->getJson("/api/items?barcode=$barcode");

    $response->assertStatus(200);
    $response->assertJson(fn (AssertableJson $itemList) =>
        $itemList->has(10)->each(fn (AssertableJson $item) =>
            $item->where('product.barcode', $barcode)->etc()
        )
    );
});

test('items index can be filtered by remaining and wasted', function () {
    Product::factory()
        ->count(3)
        ->hasItems(10)
        ->create();

    foreach (['remaining', 'wasted'] as $filter) {
        // test that we can filter for *and* against each kind
        foreach ([0, 1] as $choice) {
            $response = $this->getJson("/api/items?$filter=$choice");

            $response->assertStatus(200);
            $response->assertJson(fn (AssertableJson $itemList) =>
                $itemList->each(fn (AssertableJson $item) => (
                    match ($choice) {
                        0 => $item->where("percent_$filter", 0),
                        1 => $item->whereNot("percent_$filter", 0)
                    }
                )->etc())
            );
        }
    }
});
