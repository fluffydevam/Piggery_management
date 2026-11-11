<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cage extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_GESTATION = 'gestation';
    public const TYPE_FARROWING = 'farrowing';
    public const TYPE_BOAR = 'boar';
    public const TYPE_WEANING = 'weaning';
    public const TYPE_GROWER = 'grower';
    public const TYPE_QUARANTINE = 'quarantine';

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_OCCUPIED = 'occupied';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_INACTIVE = 'inactive';

    public const TYPES = [
        self::TYPE_GESTATION,
        self::TYPE_FARROWING,
        self::TYPE_BOAR,
        self::TYPE_WEANING,
        self::TYPE_GROWER,
        self::TYPE_QUARANTINE,
    ];

    public const STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_OCCUPIED,
        self::STATUS_MAINTENANCE,
        self::STATUS_INACTIVE,
    ];

    protected $fillable = [
        'cage_number',
        'type',
        'capacity',
        'location',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    public function pigs(): HasMany
    {
        return $this->hasMany(Pig::class);
    }

    public function activePigs(): HasMany
    {
        return $this->hasMany(Pig::class)->where('status', Pig::STATUS_ACTIVE);
    }

    public function litters(): HasMany
    {
        return $this->hasMany(Litter::class);
    }

    public function occupancyCount(): int
    {
        return $this->activePigs()->count();
    }

    public function isFull(): bool
    {
        return $this->occupancyCount() >= $this->capacity;
    }

    /**
     * Flip between available/occupied based on the active pig count.
     * Maintenance and inactive cages are never auto-changed.
     */
    public function syncOccupancyStatus(): void
    {
        if (! in_array($this->status, [self::STATUS_AVAILABLE, self::STATUS_OCCUPIED], true)) {
            return;
        }

        $this->update([
            'status' => $this->occupancyCount() > 0 ? self::STATUS_OCCUPIED : self::STATUS_AVAILABLE,
        ]);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }
}
