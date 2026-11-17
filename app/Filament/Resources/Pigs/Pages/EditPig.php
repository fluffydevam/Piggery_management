<?php

namespace App\Filament\Resources\Pigs\Pages;

use App\Filament\Resources\Pigs\PigResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPig extends EditRecord
{
    protected static string $resource = PigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
