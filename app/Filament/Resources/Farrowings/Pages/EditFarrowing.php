<?php

namespace App\Filament\Resources\Farrowings\Pages;

use App\Filament\Resources\Farrowings\FarrowingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFarrowing extends EditRecord
{
    protected static string $resource = FarrowingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
