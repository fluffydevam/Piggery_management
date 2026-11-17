<?php

namespace App\Filament\Resources\FeedTransactions\Pages;

use App\Filament\Resources\FeedTransactions\FeedTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeedTransactions extends ListRecords
{
    protected static string $resource = FeedTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
