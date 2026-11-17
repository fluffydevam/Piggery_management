<?php

namespace App\Filament\Resources\Farrowings\Pages;

use App\Filament\Resources\Farrowings\FarrowingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFarrowings extends ListRecords
{
    protected static string $resource = FarrowingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
