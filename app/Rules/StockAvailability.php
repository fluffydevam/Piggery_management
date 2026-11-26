<?php

namespace App\Rules;

use App\Models\Feed;
use App\Models\FeedTransaction;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class StockAvailability implements ValidationRule
{
    public function __construct(
        private readonly int|string|null $feedId,
        private readonly string $type,
        private readonly ?int $ignoreTransactionId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->type !== FeedTransaction::TYPE_OUT || $value === null || $value === '' || ! $this->feedId) {
            return;
        }

        $feed = Feed::find($this->feedId);

        if (! $feed instanceof Feed) {
            return; // let the feed_id existence rule handle this
        }

        $availableBags = (float) $feed->stock_in_bags;

        // When editing an existing "out" transaction, its own quantity is
        // available again for the purposes of this check.
        if ($this->ignoreTransactionId) {
            $existing = FeedTransaction::find($this->ignoreTransactionId);

            if ($existing
                && $existing->type === FeedTransaction::TYPE_OUT
                && (int) $existing->feed_id === (int) $this->feedId) {
                $availableBags += $existing->quantity_bags !== null
                    ? (float) $existing->quantity_bags
                    : ((float) $feed->bag_weight_kg > 0
                        ? (float) $existing->quantity_kg / (float) $feed->bag_weight_kg
                        : 0);
            }
        }

        if ((float) $value > $availableBags) {
            $bags = number_format($availableBags, 2).' '.Str::plural('bag', $availableBags);

            $fail("Only {$bags} (".number_format((float) $feed->stock_kg, 2)." kg) of {$feed->name} is in stock.");
        }
    }
}
