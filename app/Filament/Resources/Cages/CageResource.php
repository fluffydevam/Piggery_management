<?php

namespace App\Filament\Resources\Cages;

use App\Filament\Resources\Cages\Pages\CreateCage;
use App\Filament\Resources\Cages\Pages\EditCage;
use App\Filament\Resources\Cages\Pages\ListCages;
use App\Filament\Resources\Cages\Schemas\CageForm;
use App\Filament\Resources\Cages\Tables\CagesTable;
use App\Models\Cage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CageResource extends Resource
{
    protected static ?string $model = Cage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Cages';

    protected static string|\UnitEnum|null $navigationGroup = 'Facilities';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'cage_number';

    public static function form(Schema $schema): Schema
    {
        return CageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CagesTable::configure($table);
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
            'index' => ListCages::route('/'),
            'create' => CreateCage::route('/create'),
            'edit' => EditCage::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
