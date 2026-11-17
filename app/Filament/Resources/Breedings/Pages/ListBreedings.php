<?php

namespace App\Filament\Resources\Breedings\Pages;

use App\Filament\Resources\Breedings\BreedingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBreedings extends ListRecords
{
    protected static string $resource = BreedingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
