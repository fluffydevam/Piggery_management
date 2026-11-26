<?php

namespace App\Filament\Resources\FeedTransactions\Pages;

use App\Filament\Resources\FeedTransactions\FeedTransactionResource;
use App\Models\Feed;
use App\Models\FeedTransaction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeedTransaction extends EditRecord
{
    protected static string $resource = FeedTransactionResource::class;

    private ?int $originalFeedId = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(function (FeedTransaction $record): void {
                    Feed::find($record->feed_id)?->recalculateStock();
                }),
        ];
    }

    protected function beforeSave(): void
    {
        $original = $this->getRecord()->getOriginal('feed_id');

        $this->originalFeedId = $original !== null ? (int) $original : null;
    }

    protected function afterSave(): void
    {
        $currentFeedId = (int) $this->getRecord()->feed_id;

        Feed::find($currentFeedId)?->recalculateStock();

        if ($this->originalFeedId && $this->originalFeedId !== $currentFeedId) {
            Feed::find($this->originalFeedId)?->recalculateStock();
        }
    }
}
