<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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

        $this->call([
            CageSeeder::class,
            PigSeeder::class,
            BreedingSeeder::class,
            FeedSeeder::class,
            MedicationSeeder::class,
            MedicalLogSeeder::class,
        ]);
    }
}
