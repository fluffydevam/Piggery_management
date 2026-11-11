<?php

namespace App\Filament\Resources\MedicalLogs\Pages;

use App\Filament\Resources\MedicalLogs\MedicalLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMedicalLog extends CreateRecord
{
    protected static string $resource = MedicalLogResource::class;
}
