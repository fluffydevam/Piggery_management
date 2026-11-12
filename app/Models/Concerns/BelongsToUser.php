<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Scopes a model to the authenticated account.
 *
 * Every query is automatically constrained to rows owned by the current user,
 * and new rows inherit that user on creation. Console commands, seeders and
 * tests without an authenticated user see the unscoped data, which is what
 * lets the demo seeders run.
 */
trait BelongsToUser
{
    public static function bootBelongsToUser(): void
    {
        static::creating(function (Model $model): void {
            if (Auth::hasUser()) {
                $model->setAttribute('user_id', Auth::id());
            }
        });

        static::addGlobalScope('userOwned', function (Builder $builder): void {
            if (! Auth::hasUser()) {
                return;
            }

            $model = $builder->getModel();
            $column = $model->qualifyColumn('user_id');

            if (static::includesSharedRecords()) {
                $builder->where(function (Builder $query) use ($column): void {
                    $query->where($column, Auth::id())->orWhereNull($column);
                });

                return;
            }

            $builder->where($column, Auth::id());
        });
    }

    /**
     * Override in models that also expose rows owned by nobody (the built-in
     * medication catalog).
     */
    protected static function includesSharedRecords(): bool
    {
        return false;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Query every account's rows. Only for maintenance tasks, never for
     * request-time code.
     */
    public static function withoutUserScope(): Builder
    {
        return static::withoutGlobalScope('userOwned');
    }
}
