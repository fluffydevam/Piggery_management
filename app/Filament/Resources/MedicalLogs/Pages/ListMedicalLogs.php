<?php

namespace App\Filament\Resources\MedicalLogs\Pages;

use App\Filament\Resources\MedicalLogs\MedicalLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedicalLogs extends ListRecords
{
    protected static string $resource = MedicalLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
