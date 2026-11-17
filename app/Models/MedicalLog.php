<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class MedicalLog extends Model
{
    use HasFactory;

    public const TYPE_VACCINE = 'vaccine';
    public const TYPE_VITAMIN = 'vitamin';
    public const TYPE_DEWORMER = 'dewormer';
    public const TYPE_TREATMENT = 'treatment';
    public const TYPE_OTHER = 'other';

    public const TYPES = [
        self::TYPE_VACCINE,
        self::TYPE_VITAMIN,
        self::TYPE_DEWORMER,
        self::TYPE_TREATMENT,
        self::TYPE_OTHER,
    ];

    public const ROUTES = ['im', 'iv', 'sc', 'oral', 'topical'];

    protected $fillable = [
        'treatable_type',
        'treatable_id',
        'medication_id',
        'type',
        'date_administered',
        'dosage',
        'route',
        'administered_by',
        'next_due_date',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_administered' => 'date',
            'next_due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function treatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    protected function subjectLabel(): Attribute
    {
        return Attribute::get(function (): string {
            $treatable = $this->treatable;

            if ($treatable instanceof Pig) {
                return $treatable->tag_number.' ('.ucfirst($treatable->type).')';
            }

            if ($treatable instanceof Litter) {
                $sowTag = $treatable->sow?->tag_number;

                return ($treatable->litter_tag ?? 'Litter').($sowTag ? " — sow {$sowTag}" : '');
            }

            return 'Unknown';
        });
    }

    public function isOverdue(): bool
    {
        return $this->completed_at === null
            && $this->next_due_date !== null
            && $this->next_due_date->lt(today());
    }

    public function scopeDueWithin(Builder $query, int $days = 7): Builder
    {
        return $query->whereNotNull('next_due_date')
            ->whereNull('completed_at')
            ->whereBetween('next_due_date', [
                today()->toDateString(),
                today()->addDays($days)->toDateString(),
            ]);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('next_due_date')
            ->whereNull('completed_at')
            ->where('next_due_date', '<', today()->toDateString());
    }

    public function scopeScheduled(Builder $query, int $days = 7): Builder
    {
        return $query->whereNull('completed_at')
            ->where('date_administered', '>', today()->toDateString())
            ->where('date_administered', '<=', today()->addDays($days)->toDateString());
    }

    /**
     * Mark this schedule as completed (e.g. the booster was given).
     */
    public function markAdministered(): void
    {
        $this->update(['completed_at' => now()]);
    }

    /**
     * Complete this due booster and create the follow-up record,
     * optionally scheduling the next dose.
     */
    public function administerBooster(?string $nextDueDate = null, array $attributes = []): MedicalLog
    {
        return DB::transaction(function () use ($nextDueDate, $attributes) {
            $this->update(['completed_at' => now()]);

            return static::create(array_merge([
                'treatable_type' => $this->treatable_type,
                'treatable_id' => $this->treatable_id,
                'medication_id' => $this->medication_id,
                'type' => $this->type,
                'date_administered' => today()->toDateString(),
                'dosage' => $this->dosage,
                'route' => $this->route,
                'administered_by' => $this->administered_by,
                'next_due_date' => $nextDueDate,
                'notes' => $this->notes,
            ], $attributes));
        });
    }
}
