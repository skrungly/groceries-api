<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function store(Request $request)
    {
        $item = Item::create($request->all());
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
        $item->update($request->all());
        return response()->json($item, 201);
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return response()->json(null, 204);
    }
}
