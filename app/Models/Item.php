<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'product_id',
        'expires_at',
        'opened_at',
        'percent_remaining',
        'percent_wasted',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * begin querying Items, adding a `soonest_expiry` attribute.
     *
     * the `soonest_expiry` attribute may be equal to `expires_at` or
     * `opened_at` + the Product shelf life. it may also be `null` if
     * neither of these can be determined.
     *
     * eagerly loads the `products` relation.
     */
    public static function withSoonestExpiry(): Builder
    {
        // behold: perhaps not the most efficient way to do this, but it'll
        // do for now in case we want to sort and paginate by soonest_expiry
        return static::with('product')
            ->join('products', 'items.product_id', 'products.id')
            ->select('items.*')
            ->selectRaw('
                COALESCE(
                    LEAST(
                        items.expires_at,
                        items.opened_at + INTERVAL products.shelf_life_opened DAY
                    ),
                    items.expires_at,
                    items.opened_at + INTERVAL products.shelf_life_opened DAY
                ) as soonest_expiry');
    }
}
