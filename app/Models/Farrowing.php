<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Farrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'breeding_id',
        'farrowing_date',
        'born_alive',
        'stillborn',
        'mummified',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'farrowing_date' => 'date',
            'born_alive' => 'integer',
            'stillborn' => 'integer',
            'mummified' => 'integer',
        ];
    }

    public function breeding(): BelongsTo
    {
        return $this->belongsTo(Breeding::class);
    }

    public function litter(): HasOne
    {
        return $this->hasOne(Litter::class);
    }

    protected function sow(): Attribute
    {
        return Attribute::get(fn () => $this->breeding?->sow);
    }

    protected function totalBorn(): Attribute
    {
        return Attribute::get(fn () => $this->born_alive + $this->stillborn + $this->mummified);
    }

    protected function survivalRate(): Attribute
    {
        return Attribute::get(function (): ?float {
            $total = $this->total_born;

            return $total > 0 ? round($this->born_alive / $total * 100, 1) : null;
        });
    }
}
