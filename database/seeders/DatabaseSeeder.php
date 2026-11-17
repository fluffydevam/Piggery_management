<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        // Demo farm data is only seeded outside production. On the live
        // Supabase database, seed the admin account only.
        if (app()->isProduction()) {
            return;
        }

        // The built-in medication catalog is owned by nobody (user_id NULL),
        // so every account can use it. Seed it before impersonating the admin.
        $this->call(MedicationSeeder::class);

        $admin = User::query()
            ->where('email', env('ADMIN_EMAIL', 'farmer@example.com'))
            ->first();

        if ($admin !== null) {
            // Demo rows inherit this owner through the BelongsToUser trait.
            Auth::setUser($admin);
        }

        $this->call([
            CageSeeder::class,
            PigSeeder::class,
            BreedingSeeder::class,
            FeedSeeder::class,
            MedicalLogSeeder::class,
        ]);

        Auth::forgetUser();
    }
}
