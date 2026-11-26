<?php

namespace App\Filament\Resources\FeedTransactions\Pages;

use App\Filament\Resources\FeedTransactions\FeedTransactionResource;
use App\Models\Feed;
use Filament\Resources\Pages\CreateRecord;

class CreateFeedTransaction extends CreateRecord
{
    protected static string $resource = FeedTransactionResource::class;

    protected function afterCreate(): void
    {
        // Explicit recalculation keeps the feed cache correct even if model
        // events are bypassed by a future save path.
        Feed::find($this->getRecord()->feed_id)?->recalculateStock();
    }
}
