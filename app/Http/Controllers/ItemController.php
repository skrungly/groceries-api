<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Validation\Rule;
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

    // map: sort option => 'select' table prefix. the first option in
    // this array is used as the default sort option.
    private const SORT_OPTIONS = [
        'soonest_expiry' => '',
        'created_at' => 'items.',
        'updated_at' => 'items.',
        'name' => 'products.'
    ];

    public function store(Request $request)
    {
        $itemInfo = $request->validate(array_merge(
            self::VALIDATION_RULES,
            ['product_id' => ['required', 'uuid', 'exists:products,id']]
        ));

        // standardise the format going into the database
        $itemInfo['expires_at'] = $request->date('expires_at');
        $itemInfo['opened_at'] = $request->date('opened_at');

        $item = Item::create($itemInfo);

        return response()->json($item, 201);
    }

    public function index(Request $request)
    {
        $sort_option_regex = (
            '/^-?' . implode('|', array_keys(self::SORT_OPTIONS)) . '$/'
        );

        $options = $request->validate([
            'sort' => ['nullable', "regex:$sort_option_regex"],
            'remaining' => ['nullable', 'boolean'],
            'wasted' => ['nullable', 'boolean'],
            'barcode' => ['nullable'],
        ]);

        $options['sort'] ??= array_keys(self::SORT_OPTIONS)[0];
        $sortDirection = "ASC";

        // allow using a leading '-' to designate reverse sorting
        if (str_starts_with($options['sort'], '-')) {
            $options['sort'] = substr($options['sort'], 1);
            $sortDirection = "DESC";
        }

        $tablePrefix = self::SORT_OPTIONS[$options['sort']];

        $query = Item::withSoonestExpiry()
            ->orderByRaw('ISNULL(' . $tablePrefix . $options['sort'] . ')')
            ->orderBy($tablePrefix . $options['sort'], $sortDirection);

        if ($options['sort'] !== 'name') {
            $query = $query->orderBy('products.name');
        }

        // filter options
        foreach (['remaining', 'wasted'] as $filter) {
            if (!isset($options[$filter])) {
                continue;
            }

            $cmp = $options[$filter] ? ">" : "=";
            $query = $query->where("items.percent_$filter", $cmp, "0");
        }

        if (isset($options['barcode'])) {
            $query = $query->where("products.barcode", $options['barcode']);
        }

        return response()->json($query->get());
    }

    public function show(string $id)
    {
        $item = Item::withSoonestExpiry()->findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, Item $item)
    {
        $itemInfo = $request->validate(array_merge(
            self::VALIDATION_RULES,
            ['product_id' => ['exclude']]
        ));

        // standardise the format going into the database
        $itemInfo['expires_at'] = $request->date('expires_at');
        $itemInfo['opened_at'] = $request->date('opened_at');

        $item->update($itemInfo);

        return response()->json($item, 201);
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return response()->json(null, 204);
    }
}
