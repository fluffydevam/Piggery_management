<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medication extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPES = [
        'vaccine',
        'vitamin',
        'dewormer',
        'antibiotic',
        'treatment',
        'supplement',
    ];

    protected $fillable = [
        'name',
        'type',
        'default_dosage',
        'withdrawal_days',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'withdrawal_days' => 'integer',
        ];
    }

    public function medicalLogs(): HasMany
    {
        return $this->hasMany(MedicalLog::class);
    }
}
