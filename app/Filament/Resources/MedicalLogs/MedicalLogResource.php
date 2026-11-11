<?php

namespace App\Filament\Resources\MedicalLogs;

use App\Filament\Resources\MedicalLogs\Pages\CreateMedicalLog;
use App\Filament\Resources\MedicalLogs\Pages\EditMedicalLog;
use App\Filament\Resources\MedicalLogs\Pages\ListMedicalLogs;
use App\Filament\Resources\MedicalLogs\Schemas\MedicalLogForm;
use App\Filament\Resources\MedicalLogs\Tables\MedicalLogsTable;
use App\Models\MedicalLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MedicalLogResource extends Resource
{
    protected static ?string $model = MedicalLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?string $navigationLabel = 'Medical logs';

    protected static string|\UnitEnum|null $navigationGroup = 'Health';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return MedicalLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicalLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedicalLogs::route('/'),
            'create' => CreateMedicalLog::route('/create'),
            'edit' => EditMedicalLog::route('/{record}/edit'),
        ];
    }
}
