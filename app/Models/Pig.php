<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pig extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_SOW = 'sow';
    public const TYPE_BOAR = 'boar';
    public const TYPE_GILT = 'gilt';
    public const TYPE_PIGLET = 'piglet';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SOLD = 'sold';
    public const STATUS_CULLED = 'culled';
    public const STATUS_DECEASED = 'deceased';

    public const TYPES = [
        self::TYPE_SOW,
        self::TYPE_BOAR,
        self::TYPE_GILT,
        self::TYPE_PIGLET,
    ];

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_SOLD,
        self::STATUS_CULLED,
        self::STATUS_DECEASED,
    ];

    protected $fillable = [
        'tag_number',
        'type',
        'breed',
        'sex',
        'birth_date',
        'cage_id',
        'status',
        'origin',
        'photo_path',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $pig): void {
            // Re-sync the previous cage as well when the pig moves.
            if ($pig->wasChanged('cage_id')) {
                $previousCageId = $pig->getOriginal('cage_id');

                if ($previousCageId !== null && (int) $previousCageId !== (int) $pig->cage_id) {
                    Cage::find($previousCageId)?->syncOccupancyStatus();
                }
            }

            Cage::find($pig->cage_id)?->syncOccupancyStatus();
        });

        static::deleted(function (self $pig): void {
            Cage::find($pig->cage_id)?->syncOccupancyStatus();
        });
    }

    public function cage(): BelongsTo
    {
        return $this->belongsTo(Cage::class);
    }

    /** Breedings where this pig is the sow. */
    public function breedingsAsSow(): HasMany
    {
        return $this->hasMany(Breeding::class, 'sow_id');
    }

    /** Breedings where this pig is the boar. */
    public function breedingsAsBoar(): HasMany
    {
        return $this->hasMany(Breeding::class, 'boar_id');
    }

    public function latestBreeding(): HasOne
    {
        return $this->hasOne(Breeding::class, 'sow_id')->latestOfMany('breeding_date');
    }

    public function medicalLogs(): MorphMany
    {
        return $this->morphMany(MedicalLog::class, 'treatable');
    }

    protected function ageInMonths(): Attribute
    {
        return Attribute::get(
            fn () => $this->birth_date
                ? (int) $this->birth_date->diffInMonths(now())
                : null
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeSows(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_SOW);
    }

    public function scopeBoars(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_BOAR);
    }

    public function scopeFemale(Builder $query): Builder
    {
        return $query->where('sex', 'female');
    }

    public function scopeMale(Builder $query): Builder
    {
        return $query->where('sex', 'male');
    }
}
