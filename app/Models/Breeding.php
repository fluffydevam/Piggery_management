<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Breeding extends Model
{
    use BelongsToUser;
    use HasFactory;
    use SoftDeletes;

    /** Average porcine gestation period, in days. */
    public const GESTATION_DAYS = 114;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_NOT_PREGNANT = 'not_pregnant';
    public const STATUS_ABORTED = 'aborted';
    public const STATUS_FARROWED = 'farrowed';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_NOT_PREGNANT,
        self::STATUS_ABORTED,
        self::STATUS_FARROWED,
    ];

    protected $fillable = [
        'user_id',
        'sow_id',
        'boar_id',
        'breeding_date',
        'method',
        'semen_source',
        'expected_farrowing_date',
        'pregnancy_status',
        'confirmed_pregnant_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'breeding_date' => 'date',
            'expected_farrowing_date' => 'date',
            'confirmed_pregnant_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $breeding) => $breeding->computeExpectedFarrowingDate());
        static::updating(fn (self $breeding) => $breeding->computeExpectedFarrowingDate());
    }

    /**
     * expected_farrowing_date is always derived from breeding_date (+114 days).
     */
    public function computeExpectedFarrowingDate(): void
    {
        if ($this->breeding_date !== null) {
            $this->expected_farrowing_date = $this->breeding_date->copy()->addDays(self::GESTATION_DAYS);
        }
    }

    public function sow(): BelongsTo
    {
        return $this->belongsTo(Pig::class, 'sow_id');
    }

    public function boar(): BelongsTo
    {
        return $this->belongsTo(Pig::class, 'boar_id');
    }

    public function farrowing(): HasOne
    {
        return $this->hasOne(Farrowing::class);
    }

    public function litter(): HasOneThrough
    {
        return $this->hasOneThrough(Litter::class, Farrowing::class);
    }

    /**
     * Record the delivery outcome: creates the farrowing + initial litter and
     * marks this breeding as farrowed, all inside a single transaction.
     */
    public function recordFarrowing(array $attributes): Farrowing
    {
        return DB::transaction(function () use ($attributes) {
            $farrowing = $this->farrowing()->create([
                'user_id' => $this->user_id,
                'farrowing_date' => $attributes['farrowing_date'] ?? $this->expected_farrowing_date->toDateString(),
                'born_alive' => (int) ($attributes['born_alive'] ?? 0),
                'stillborn' => (int) ($attributes['stillborn'] ?? 0),
                'mummified' => (int) ($attributes['mummified'] ?? 0),
                'notes' => $attributes['notes'] ?? null,
            ]);

            $farrowing->litter()->create([
                'user_id' => $this->user_id,
                'cage_id' => $attributes['cage_id'] ?? null,
                'litter_tag' => $attributes['litter_tag']
                    ?? $this->sow->tag_number.'-'.$farrowing->farrowing_date->format('Ymd'),
                'current_count' => (int) ($attributes['born_alive'] ?? 0),
            ]);

            $this->forceFill(['pregnancy_status' => self::STATUS_FARROWED])->save();

            return $farrowing->load('litter');
        });
    }

    public function isPregnant(): bool
    {
        return in_array($this->pregnancy_status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true);
    }

    protected function daysUntilFarrowing(): Attribute
    {
        return Attribute::get(function (): ?int {
            if ($this->expected_farrowing_date === null) {
                return null;
            }

            return (int) now()->startOfDay()
                ->diffInDays($this->expected_farrowing_date->copy()->startOfDay(), false);
        });
    }

    public function scopePregnant(Builder $query): Builder
    {
        return $query->whereIn('pregnancy_status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    public function scopeUpcomingFarrowing(Builder $query, int $days = 7): Builder
    {
        return $query->pregnant()
            ->whereBetween('expected_farrowing_date', [
                today()->toDateString(),
                today()->addDays($days)->toDateString(),
            ]);
    }

    public function scopeOverdueFarrowing(Builder $query): Builder
    {
        return $query->pregnant()
            ->where('expected_farrowing_date', '<', today()->toDateString());
    }
}
