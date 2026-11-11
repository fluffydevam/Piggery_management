<?php

namespace App\Filament\Resources\Breedings\Pages;

use App\Filament\Resources\Breedings\BreedingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditBreeding extends EditRecord
{
    protected static string $resource = BreedingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
