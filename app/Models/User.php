<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Every account that registers owns one isolated piggery. Access is still
     * gated by authentication; there are no roles in v1.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function cages(): HasMany
    {
        return $this->hasMany(Cage::class);
    }

    public function pigs(): HasMany
    {
        return $this->hasMany(Pig::class);
    }

    public function breedings(): HasMany
    {
        return $this->hasMany(Breeding::class);
    }

    public function litters(): HasMany
    {
        return $this->hasMany(Litter::class);
    }

    public function feeds(): HasMany
    {
        return $this->hasMany(Feed::class);
    }

    public function medicalLogs(): HasMany
    {
        return $this->hasMany(MedicalLog::class);
    }
}
