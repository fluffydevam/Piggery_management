<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medication extends Model
{
    use BelongsToUser;
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
        'user_id',
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

    /**
     * Rows with a NULL owner are the built-in catalog and are visible to
     * every account; rows with an owner are private additions.
     */
    protected static function includesSharedRecords(): bool
    {
        return true;
    }
}
