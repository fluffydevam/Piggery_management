<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Feed extends Model
{
    use BelongsToUser;
    use HasFactory;
    use SoftDeletes;

    public const CATEGORIES = [
        'starter',
        'grower',
        'finisher',
        'gestating',
        'lactating',
        'boar',
        'supplement',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'bag_weight_kg',
        'stock_kg',
        'reorder_level_bags',
        'unit_cost',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'bag_weight_kg' => 'decimal:2',
            'stock_kg' => 'decimal:2',
            'reorder_level_bags' => 'decimal:2',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FeedTransaction::class)->latest('transaction_date');
    }

    protected function stockInBags(): Attribute
    {
        return Attribute::get(
            fn () => (float) $this->bag_weight_kg > 0
                ? round((float) $this->stock_kg / (float) $this->bag_weight_kg, 2)
                : 0.0
        );
    }

    /** The kg threshold behind the farmer-facing reorder level in bags. */
    public function reorderThresholdKg(): float
    {
        return round((float) $this->reorder_level_bags * (float) $this->bag_weight_kg, 2);
    }

    public function stockLabel(): string
    {
        return $this->bagLabel((float) $this->stock_in_bags, (float) $this->stock_kg);
    }

    public function reorderLevelLabel(): string
    {
        return $this->bagLabel((float) $this->reorder_level_bags, $this->reorderThresholdKg());
    }

    public function isLowStock(): bool
    {
        return (float) $this->stock_kg <= $this->reorderThresholdKg();
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereRaw('stock_kg <= (reorder_level_bags * bag_weight_kg)');
    }

    protected function bagLabel(float $bags, float $kg): string
    {
        return number_format($bags, 2).' '.Str::plural('bag', $bags).' ('.number_format($kg, 2).' kg)';
    }

    /**
     * Recompute stock_kg from the transaction ledger.
     * Stock in adds, stock out subtracts, adjustments are signed deltas.
     */
    public function recalculateStock(): void
    {
        DB::transaction(function () {
            $feed = static::query()->lockForUpdate()->findOrFail($this->getKey());

            $stock = $feed->transactions()->toBase()
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'out' THEN -quantity_kg ELSE quantity_kg END), 0) as stock")
                ->value('stock');

            $feed->update(['stock_kg' => $stock]);
            $this->stock_kg = $feed->stock_kg;
        });
    }
}
