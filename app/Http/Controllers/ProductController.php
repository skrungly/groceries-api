<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private const VALIDATION_RULES = [
        'user_id' => ['exclude'],  // for now
        'shelf_life_opened' => ['nullable', 'integer'],
        'quantity' => ['nullable', 'integer'],
        'cost' => ['nullable', 'numeric'],
        'weight' => ['nullable', 'integer'],
    ];

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge(
            self::VALIDATION_RULES,
            [
                'name' => ['required', 'max:128'],
                'barcode' => ['required', 'max:128'],
            ]
        ));

        $product = Product::create($validated);
        return response()->json($product, 201);
    }

    public function index(Request $request)
    {
        // TODO: add other filter and sorting options
        $barcode = $request->query('barcode');

        if ($barcode) {
            $products = Product::where('barcode', $barcode)->get();
        } else {
            $products = Product::all();
        }

        return response()->json($products);
    }

    public function show(Product $product)
    {
        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate(array_merge(
            self::VALIDATION_RULES,
            [
                'name' => ['nullable', 'max:128'],
                'barcode' => ['exclude'],
            ]
        ));

        $product->update($validated);
        return response()->json($product, 201);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(null, 204);
    }
}
