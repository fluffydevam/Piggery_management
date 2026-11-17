<?php

namespace App\Filament\Resources\Cages\Pages;

use App\Filament\Resources\Cages\CageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCages extends ListRecords
{
    protected static string $resource = CageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
