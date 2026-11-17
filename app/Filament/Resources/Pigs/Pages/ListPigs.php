<?php

namespace App\Filament\Resources\Pigs\Pages;

use App\Filament\Resources\Pigs\PigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPigs extends ListRecords
{
    protected static string $resource = PigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
