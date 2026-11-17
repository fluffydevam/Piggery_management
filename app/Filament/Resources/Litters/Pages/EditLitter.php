<?php

namespace App\Filament\Resources\Litters\Pages;

use App\Filament\Resources\Litters\LitterResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditLitter extends EditRecord
{
    protected static string $resource = LitterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
