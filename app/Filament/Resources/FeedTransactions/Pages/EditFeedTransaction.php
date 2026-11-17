<?php

namespace App\Filament\Resources\FeedTransactions\Pages;

use App\Filament\Resources\FeedTransactions\FeedTransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeedTransaction extends EditRecord
{
    protected static string $resource = FeedTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
