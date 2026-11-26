<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedTransaction extends Model
{
    use BelongsToUser;
    use HasFactory;

    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPES = [self::TYPE_IN, self::TYPE_OUT, self::TYPE_ADJUSTMENT];

    protected $fillable = [
        'feed_id',
        'type',
        'quantity_kg',
        'quantity_bags',
        'transaction_date',
        'reference',
        'cost',
        'user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'quantity_kg' => 'decimal:2',
            'quantity_bags' => 'decimal:2',
            'cost' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        // Bags are what the farmer records; kg is the stored ledger unit.
        static::saving(function (self $transaction): void {
            if ($transaction->quantity_bags === null) {
                return;
            }

            $bagWeight = (float) (Feed::find($transaction->feed_id)?->bag_weight_kg ?? 0);

            if ($bagWeight > 0) {
                $transaction->quantity_kg = round((float) $transaction->quantity_bags * $bagWeight, 2);
            }
        });

        // Always look the feed up fresh: the relation may still point at the
        // old feed after a feed_id change, and its aggregate would be stale.
        static::created(fn (self $transaction) => Feed::find($transaction->feed_id)?->recalculateStock());

        static::updated(function (self $transaction): void {
            Feed::find($transaction->feed_id)?->recalculateStock();

            // Moving a transaction to another feed must also fix the old feed.
            $originalFeedId = $transaction->getOriginal('feed_id');

            if ($originalFeedId && (int) $originalFeedId !== (int) $transaction->feed_id) {
                Feed::find($originalFeedId)?->recalculateStock();
            }
        });

        static::deleted(fn (self $transaction) => Feed::find($transaction->feed_id)?->recalculateStock());
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    public function signedQuantity(): float
    {
        return $this->type === self::TYPE_OUT
            ? -(float) $this->quantity_kg
            : (float) $this->quantity_kg;
    }

    public function signedQuantityInBags(): float
    {
        return $this->type === self::TYPE_OUT
            ? -abs((float) $this->quantity_bags)
            : (float) $this->quantity_bags;
    }
}
