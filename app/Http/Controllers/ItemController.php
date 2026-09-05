<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    private const VALIDATION_RULES = [
        'user_id' => ['exclude'],  // for now
        'expires_at' => ['nullable', 'date'],
        'opened_at' => ['nullable', 'date'],
        'percent_remaining' => ['nullable', 'integer'],
        'percent_wasted' => ['nullable', 'integer'],
    ];

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge(
            self::VALIDATION_RULES,
            ['product_id' => ['required', 'uuid', 'exists:products,id']]
        ));

        $item = Item::create($validated);
        return response()->json($item, 201);
    }

    public function index()
    {
        return response()->json(Item::with('product')->get());
    }

    public function show(string $id)
    {
        $item = Item::with('product')->findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate(array_merge(
            self::VALIDATION_RULES,
            ['product_id' => ['exclude']]
        ));

        $item->update($validated);
        return response()->json($item, 201);
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return response()->json(null, 204);
    }
}
