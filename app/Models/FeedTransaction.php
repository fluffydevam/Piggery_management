<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedTransaction extends Model
{
    use HasFactory;

    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPES = [self::TYPE_IN, self::TYPE_OUT, self::TYPE_ADJUSTMENT];

    protected $fillable = [
        'feed_id',
        'type',
        'quantity_kg',
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
            'cost' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $transaction): void {
            $transaction->user_id ??= auth()->id();
        });

        static::created(fn (self $transaction) => $transaction->feed->recalculateStock());
        static::deleted(fn (self $transaction) => $transaction->feed->recalculateStock());
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function signedQuantity(): float
    {
        return $this->type === self::TYPE_OUT
            ? -(float) $this->quantity_kg
            : (float) $this->quantity_kg;
    }
}
