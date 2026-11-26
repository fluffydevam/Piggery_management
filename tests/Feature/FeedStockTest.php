<?php

namespace Tests\Feature;

use App\Models\Feed;
use App\Models\FeedTransaction;
use App\Rules\StockAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_in_out_and_adjustment_transactions_in_bags_update_feed_stock(): void
    {
        $feed = Feed::factory()->create(['stock_kg' => 0, 'bag_weight_kg' => 50]);

        $stockIn = FeedTransaction::create([
            'feed_id' => $feed->id,
            'type' => FeedTransaction::TYPE_IN,
            'quantity_bags' => 4,
            'transaction_date' => today()->toDateString(),
        ]);

        $this->assertSame('4.00', $stockIn->quantity_bags);
        $this->assertSame('200.00', $feed->fresh()->stock_kg);
        $this->assertSame(4.0, $feed->fresh()->stock_in_bags);

        FeedTransaction::create([
            'feed_id' => $feed->id,
            'type' => FeedTransaction::TYPE_OUT,
            'quantity_bags' => 1,
            'transaction_date' => today()->toDateString(),
        ]);

        $this->assertSame('150.00', $feed->fresh()->stock_kg);

        FeedTransaction::create([
            'feed_id' => $feed->id,
            'type' => FeedTransaction::TYPE_ADJUSTMENT,
            'quantity_bags' => -0.2,
            'transaction_date' => today()->toDateString(),
        ]);

        $this->assertSame('140.00', $feed->fresh()->stock_kg);
        $this->assertSame(2.8, $feed->fresh()->stock_in_bags);
    }

    public function test_bag_weight_from_feed_details_drives_the_kg_conversion(): void
    {
        $feed = Feed::factory()->create(['stock_kg' => 0, 'bag_weight_kg' => 25]);

        $transaction = FeedTransaction::create([
            'feed_id' => $feed->id,
            'type' => FeedTransaction::TYPE_IN,
            'quantity_bags' => 3,
            'transaction_date' => today()->toDateString(),
        ]);

        $this->assertSame('75.00', $transaction->quantity_kg);
        $this->assertSame('75.00', $feed->fresh()->stock_kg);
        $this->assertSame(3.0, $feed->fresh()->stock_in_bags);
    }

    public function test_editing_a_transaction_recalculates_feed_stock(): void
    {
        $feed = Feed::factory()->create(['stock_kg' => 0, 'bag_weight_kg' => 50]);
        $transaction = FeedTransaction::create([
            'feed_id' => $feed->id,
            'type' => FeedTransaction::TYPE_IN,
            'quantity_bags' => 1,
            'transaction_date' => today()->toDateString(),
        ]);

        $this->assertSame('50.00', $feed->fresh()->stock_kg);

        $transaction->update(['quantity_bags' => 3]);

        $this->assertSame('150.00', $transaction->fresh()->quantity_kg);
        $this->assertSame('150.00', $feed->fresh()->stock_kg);
        $this->assertSame(3.0, $feed->fresh()->stock_in_bags);
    }

    public function test_moving_a_transaction_to_another_feed_recalculates_both_stocks(): void
    {
        $first = Feed::factory()->create(['stock_kg' => 0, 'bag_weight_kg' => 50]);
        $second = Feed::factory()->create(['stock_kg' => 0, 'bag_weight_kg' => 25]);

        $transaction = FeedTransaction::create([
            'feed_id' => $first->id,
            'type' => FeedTransaction::TYPE_IN,
            'quantity_bags' => 1,
            'transaction_date' => today()->toDateString(),
        ]);

        $this->assertSame('50.00', $first->fresh()->stock_kg);

        $transaction->update(['feed_id' => $second->id]);

        $this->assertSame('0.00', $first->fresh()->stock_kg);
        $this->assertSame('25.00', $second->fresh()->stock_kg);
    }

    public function test_reorder_level_in_bags_sets_the_kg_alert_threshold(): void
    {
        $feed = Feed::factory()->create([
            'bag_weight_kg' => 25,
            'reorder_level_bags' => 2,
            'stock_kg' => 50,
        ]);

        $this->assertSame(50.0, $feed->reorderThresholdKg());
        $this->assertSame('2.00 bags (50.00 kg)', $feed->reorderLevelLabel());
        $this->assertTrue($feed->isLowStock());

        $feed->update(['stock_kg' => 50.01]);

        $this->assertFalse($feed->fresh()->isLowStock());
    }

    public function test_low_stock_scope_includes_stock_equal_to_the_bag_threshold(): void
    {
        $low = Feed::factory()->create(['stock_kg' => 80, 'bag_weight_kg' => 50, 'reorder_level_bags' => 2]);
        $exact = Feed::factory()->create(['stock_kg' => 100, 'bag_weight_kg' => 50, 'reorder_level_bags' => 2]);
        $healthy = Feed::factory()->create(['stock_kg' => 120, 'bag_weight_kg' => 50, 'reorder_level_bags' => 2]);

        $ids = Feed::query()->lowStock()->pluck('id');

        $this->assertTrue($ids->contains($low->id));
        $this->assertTrue($ids->contains($exact->id));
        $this->assertFalse($ids->contains($healthy->id));
    }

    public function test_stock_availability_rule_rejects_overdraw_in_bags(): void
    {
        $feed = Feed::factory()->create(['stock_kg' => 50, 'bag_weight_kg' => 50]);

        $failure = null;
        (new StockAvailability($feed->id, FeedTransaction::TYPE_OUT))
            ->validate('quantity_bags', 1.5, function (string $message) use (&$failure) {
                $failure = $message;
            });
        $this->assertNotNull($failure);

        $failure = null;
        (new StockAvailability($feed->id, FeedTransaction::TYPE_OUT))
            ->validate('quantity_bags', 0.5, function (string $message) use (&$failure) {
                $failure = $message;
            });
        $this->assertNull($failure);

        // Stock-in transactions are never limited by current stock.
        $failure = null;
        (new StockAvailability($feed->id, FeedTransaction::TYPE_IN))
            ->validate('quantity_bags', 1000, function (string $message) use (&$failure) {
                $failure = $message;
            });
        $this->assertNull($failure);
    }
}
