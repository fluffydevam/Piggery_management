<?php

namespace App\Filament\Resources\Litters\Pages;

use App\Filament\Resources\Litters\LitterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLitters extends ListRecords
{
    protected static string $resource = LitterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
