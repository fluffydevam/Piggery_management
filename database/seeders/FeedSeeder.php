<?php

namespace Database\Seeders;

use App\Models\Feed;
use App\Models\FeedTransaction;
use Illuminate\Database\Seeder;

class FeedSeeder extends Seeder
{
    public function run(): void
    {
        $feeds = [
            ['name' => 'Gestating Sow Feed', 'category' => 'gestating', 'bag_weight_kg' => 50, 'reorder_level_kg' => 150, 'unit_cost' => 1450],
            ['name' => 'Lactating Sow Feed', 'category' => 'lactating', 'bag_weight_kg' => 50, 'reorder_level_kg' => 200, 'unit_cost' => 1600],
            ['name' => 'Boar Feed', 'category' => 'boar', 'bag_weight_kg' => 50, 'reorder_level_kg' => 100, 'unit_cost' => 1350],
            ['name' => 'Starter Feed', 'category' => 'starter', 'bag_weight_kg' => 25, 'reorder_level_kg' => 75, 'unit_cost' => 1800],
            ['name' => 'Grower Feed', 'category' => 'grower', 'bag_weight_kg' => 50, 'reorder_level_kg' => 150, 'unit_cost' => 1500],
        ];

        foreach ($feeds as $feed) {
            Feed::firstOrCreate(['name' => $feed['name']], $feed);
        }

        if (FeedTransaction::query()->exists()) {
            return;
        }

        $transactions = [
            // Gestating: 600 in, 200 out => 400 kg (healthy)
            ['feed' => 'Gestating Sow Feed', 'type' => 'in', 'quantity_kg' => 600, 'days_ago' => 55, 'reference' => 'PO-1001'],
            ['feed' => 'Gestating Sow Feed', 'type' => 'out', 'quantity_kg' => 200, 'days_ago' => 20, 'reference' => 'Weekly feeding'],
            // Lactating: 500 in, 150 out => 350 kg
            ['feed' => 'Lactating Sow Feed', 'type' => 'in', 'quantity_kg' => 500, 'days_ago' => 50, 'reference' => 'PO-1002'],
            ['feed' => 'Lactating Sow Feed', 'type' => 'out', 'quantity_kg' => 150, 'days_ago' => 10, 'reference' => 'Farrowing pens'],
            // Boar: 300 in, 80 out => 220 kg
            ['feed' => 'Boar Feed', 'type' => 'in', 'quantity_kg' => 300, 'days_ago' => 45, 'reference' => 'PO-1003'],
            ['feed' => 'Boar Feed', 'type' => 'out', 'quantity_kg' => 80, 'days_ago' => 15, 'reference' => 'Boar pens'],
            // Starter: 150 in, 100 out => 50 kg (LOW, reorder level 75)
            ['feed' => 'Starter Feed', 'type' => 'in', 'quantity_kg' => 150, 'days_ago' => 40, 'reference' => 'PO-1004'],
            ['feed' => 'Starter Feed', 'type' => 'out', 'quantity_kg' => 100, 'days_ago' => 5, 'reference' => 'Creep feeding'],
            // Grower: 400 in, 120 out => 280 kg
            ['feed' => 'Grower Feed', 'type' => 'in', 'quantity_kg' => 400, 'days_ago' => 35, 'reference' => 'PO-1005'],
            ['feed' => 'Grower Feed', 'type' => 'out', 'quantity_kg' => 120, 'days_ago' => 7, 'reference' => 'Grower pens'],
        ];

        $feedIds = Feed::query()->pluck('id', 'name');

        foreach ($transactions as $transaction) {
            FeedTransaction::create([
                'feed_id' => $feedIds[$transaction['feed']],
                'type' => $transaction['type'],
                'quantity_kg' => $transaction['quantity_kg'],
                'transaction_date' => today()->subDays($transaction['days_ago'])->toDateString(),
                'reference' => $transaction['reference'],
            ]);
        }
    }
}
