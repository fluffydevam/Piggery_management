<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Litter extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const WEANING_AGE_DAYS = 21;

    public const STATUS_NURSING = 'nursing';
    public const STATUS_WEANED = 'weaned';
    public const STATUS_SOLD = 'sold';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_LOST = 'lost';

    public const STATUSES = [
        self::STATUS_NURSING,
        self::STATUS_WEANED,
        self::STATUS_SOLD,
        self::STATUS_FINISHED,
        self::STATUS_LOST,
    ];

    protected $fillable = [
        'farrowing_id',
        'cage_id',
        'litter_tag',
        'status',
        'current_count',
        'deaths_count',
        'weaned_date',
        'weaned_count',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'weaned_date' => 'date',
            'current_count' => 'integer',
            'deaths_count' => 'integer',
            'weaned_count' => 'integer',
        ];
    }

    public function farrowing(): BelongsTo
    {
        return $this->belongsTo(Farrowing::class);
    }

    public function cage(): BelongsTo
    {
        return $this->belongsTo(Cage::class);
    }

    public function medicalLogs(): MorphMany
    {
        return $this->morphMany(MedicalLog::class, 'treatable');
    }

    protected function sow(): Attribute
    {
        return Attribute::get(fn () => $this->farrowing?->breeding?->sow);
    }

    public function daysOld(): ?int
    {
        return $this->farrowing
            ? (int) $this->farrowing->farrowing_date->diffInDays(now())
            : null;
    }

    public function scopeNursing(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_NURSING);
    }

    public function scopeReadyToWean(Builder $query): Builder
    {
        $cutoff = today()->subDays(self::WEANING_AGE_DAYS)->toDateString();

        return $query->nursing()
            ->whereHas('farrowing', fn (Builder $farrowing) => $farrowing
                ->where('farrowing_date', '<=', $cutoff));
    }

    public function recordDeath(int $count = 1): void
    {
        DB::transaction(function () use ($count) {
            $this->increment('deaths_count', $count);
            $this->decrement('current_count', min($count, (int) $this->current_count));
            $this->refresh();

            if ($this->current_count <= 0) {
                $this->update(['status' => self::STATUS_LOST]);
            }
        });
    }

    public function wean(?string $date = null, ?int $count = null): void
    {
        $this->update([
            'status' => self::STATUS_WEANED,
            'weaned_date' => $date ?? today()->toDateString(),
            'weaned_count' => $count ?? $this->current_count,
        ]);
    }

    /**
     * Promote one piglet from this litter into a herd-level gilt record.
     */
    public function promoteToGilt(string $tagNumber, array $attributes = []): Pig
    {
        return Pig::create(array_merge([
            'tag_number' => $tagNumber,
            'type' => Pig::TYPE_GILT,
            'sex' => 'female',
            'birth_date' => $this->farrowing?->farrowing_date?->toDateString(),
            'breed' => $this->sow?->breed,
            'origin' => 'bred_on_farm',
            'status' => Pig::STATUS_ACTIVE,
        ], $attributes));
    }
}
