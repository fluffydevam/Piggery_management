<?php

namespace App\Listeners;

use App\Models\Cage;
use App\Models\User;
use Filament\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;

/**
 * Gives every new account a usable starting piggery: an empty set of cages
 * grouped by purpose. No animals, feed or treatments are invented, so the
 * dashboard starts on zero and the user enters their real records.
 */
class CreateStarterFarm
{
    public function handle(Registered $event): void
    {
        if (! config('farm.starter_data')) {
            return;
        }

        $user = $event->getUser();

        if (! $user instanceof User) {
            return;
        }

        DB::transaction(function () use ($user): void {
            foreach (config('farm.starter_cages', []) as $cage) {
                $user->cages()->firstOrCreate(
                    ['cage_number' => $cage['cage_number']],
                    $cage + ['status' => Cage::STATUS_AVAILABLE],
                );
            }
        });
    }
}
