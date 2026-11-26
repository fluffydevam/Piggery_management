<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feeds', function (Blueprint $table): void {
            $table->decimal('reorder_level_bags', 12, 2)->default(0)->after('bag_weight_kg');
        });

        Schema::table('feed_transactions', function (Blueprint $table): void {
            $table->decimal('quantity_bags', 12, 2)->nullable()->after('quantity_kg');
        });

        DB::table('feeds')->orderBy('id')->each(function ($feed): void {
            $bagWeight = (float) ($feed->bag_weight_kg ?? 0);

            DB::table('feeds')->where('id', $feed->id)->update([
                'reorder_level_bags' => $bagWeight > 0
                    ? round((float) $feed->reorder_level_kg / $bagWeight, 2)
                    : 0,
            ]);
        });

        DB::table('feed_transactions')->orderBy('id')->each(function ($transaction): void {
            $bagWeight = (float) (DB::table('feeds')->where('id', $transaction->feed_id)->value('bag_weight_kg') ?? 0);

            DB::table('feed_transactions')->where('id', $transaction->id)->update([
                'quantity_bags' => $bagWeight > 0
                    ? round((float) $transaction->quantity_kg / $bagWeight, 2)
                    : null,
            ]);
        });

        // Editing a transaction used to leave stock_kg stale. Rebuild it from
        // the ledger so existing feeds show the right number of bags immediately.
        DB::table('feeds')->orderBy('id')->each(function ($feed): void {
            $stock = DB::table('feed_transactions')
                ->where('feed_id', $feed->id)
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'out' THEN -quantity_kg ELSE quantity_kg END), 0) as stock")
                ->value('stock');

            DB::table('feeds')->where('id', $feed->id)->update([
                'stock_kg' => $stock,
            ]);
        });

        // reorder_level_kg is deliberately kept for one deploy so the old
        // build keeps working; 000003 removes it after the new build is live.
    }

    public function down(): void
    {
        DB::table('feeds')->orderBy('id')->each(function ($feed): void {
            DB::table('feeds')->where('id', $feed->id)->update([
                'reorder_level_kg' => round(
                    (float) $feed->reorder_level_bags * (float) ($feed->bag_weight_kg ?? 0),
                    2,
                ),
            ]);
        });

        Schema::table('feed_transactions', function (Blueprint $table): void {
            $table->dropColumn('quantity_bags');
        });

        Schema::table('feeds', function (Blueprint $table): void {
            $table->dropColumn('reorder_level_bags');
        });
    }
};
