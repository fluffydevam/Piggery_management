<?php

namespace App\Filament\Resources\MedicalLogs\Pages;

use App\Filament\Resources\MedicalLogs\MedicalLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMedicalLog extends EditRecord
{
    protected static string $resource = MedicalLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
