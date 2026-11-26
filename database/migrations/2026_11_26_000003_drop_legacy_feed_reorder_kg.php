<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run after the bag-based feed build is live, so the old build never
     * reads a column that no longer exists during the deploy window.
     */
    public function up(): void
    {
        Schema::table('feeds', function (Blueprint $table): void {
            $table->dropColumn('reorder_level_kg');
        });
    }

    public function down(): void
    {
        Schema::table('feeds', function (Blueprint $table): void {
            $table->decimal('reorder_level_kg', 12, 2)->default(0)->after('reorder_level_bags');
        });

        DB::table('feeds')->orderBy('id')->each(function ($feed): void {
            DB::table('feeds')->where('id', $feed->id)->update([
                'reorder_level_kg' => round(
                    (float) $feed->reorder_level_bags * (float) ($feed->bag_weight_kg ?? 0),
                    2,
                ),
            ]);
        });
    }
};
