<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Product;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $itemQuery = Item::join('products', 'items.product_id', 'products.id');

        $itemTotals = $itemQuery->clone()->selectRaw('
            COUNT(*) AS total_items,
            SUM(cost) AS total_items_cost,
            SUM(net_weight) AS total_items_weight
        ')->first()->toArray();

        $currentItems = $itemQuery
            ->clone()
            ->where('percent_remaining', '>', '0')
            ->selectRaw('
                COUNT(*) AS current_items,
                SUM(cost) AS current_items_cost,
                SUM(net_weight) AS current_items_weight
            ')
            ->first()
            ->toArray();

        $productCounts = Product::selectRaw('
            COUNT(*) AS products,
            COUNT(cost) AS products_with_cost,
            COUNT(net_weight) AS products_with_weight
        ')->first()->toArray();

        $wastedItems = $itemQuery
            ->clone()
            ->where('percent_wasted', '>', '0')
            ->selectRaw('
                COUNT(*) AS wasted_items,
                SUM(percent_wasted / (quantity * 100)) AS wasted_quantity,
                SUM(cost * percent_wasted / (quantity * 100)) AS wasted_cost,
                SUM(net_weight * percent_wasted / (quantity * 100)) AS wasted_weight
            ')
            ->first()
            ->toArray();

        $stats = [
            ...$itemTotals,
            ...$currentItems,
            ...$productCounts,
            ...$wastedItems,
        ];

        return response()->json($stats, 200);
    }
}
