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

    public function test_stock_in_out_and_adjustment_transactions_update_feed_stock(): void
    {
        $feed = Feed::factory()->create(['stock_kg' => 0, 'bag_weight_kg' => 50]);

        FeedTransaction::create([
            'feed_id' => $feed->id,
            'type' => FeedTransaction::TYPE_IN,
            'quantity_kg' => 200,
            'transaction_date' => today()->toDateString(),
        ]);

        $this->assertSame('200.00', $feed->fresh()->stock_kg);
        $this->assertSame(4.0, $feed->fresh()->stock_in_bags);

        FeedTransaction::create([
            'feed_id' => $feed->id,
            'type' => FeedTransaction::TYPE_OUT,
            'quantity_kg' => 50,
            'transaction_date' => today()->toDateString(),
        ]);

        $this->assertSame('150.00', $feed->fresh()->stock_kg);

        FeedTransaction::create([
            'feed_id' => $feed->id,
            'type' => FeedTransaction::TYPE_ADJUSTMENT,
            'quantity_kg' => -10,
            'transaction_date' => today()->toDateString(),
        ]);

        $this->assertSame('140.00', $feed->fresh()->stock_kg);
    }

    public function test_low_stock_scope_includes_stock_equal_to_the_reorder_level(): void
    {
        $low = Feed::factory()->create(['stock_kg' => 80, 'reorder_level_kg' => 100]);
        $exact = Feed::factory()->create(['stock_kg' => 100, 'reorder_level_kg' => 100]);
        $healthy = Feed::factory()->create(['stock_kg' => 120, 'reorder_level_kg' => 100]);

        $ids = Feed::query()->lowStock()->pluck('id');

        $this->assertTrue($ids->contains($low->id));
        $this->assertTrue($ids->contains($exact->id));
        $this->assertFalse($ids->contains($healthy->id));

        $this->assertTrue($exact->isLowStock());
        $this->assertFalse($healthy->isLowStock());
    }

    public function test_stock_availability_rule_rejects_overdraw(): void
    {
        $feed = Feed::factory()->create(['stock_kg' => 50]);

        $failure = null;
        (new StockAvailability($feed->id, FeedTransaction::TYPE_OUT))
            ->validate('quantity_kg', 60, function (string $message) use (&$failure) {
                $failure = $message;
            });
        $this->assertNotNull($failure);

        $failure = null;
        (new StockAvailability($feed->id, FeedTransaction::TYPE_OUT))
            ->validate('quantity_kg', 40, function (string $message) use (&$failure) {
                $failure = $message;
            });
        $this->assertNull($failure);

        // Stock-in transactions are never limited by current stock.
        $failure = null;
        (new StockAvailability($feed->id, FeedTransaction::TYPE_IN))
            ->validate('quantity_kg', 1000, function (string $message) use (&$failure) {
                $failure = $message;
            });
        $this->assertNull($failure);
    }
}
