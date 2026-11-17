<?php

namespace App\Filament\Resources\Farrowings;

use App\Filament\Resources\Farrowings\Pages\EditFarrowing;
use App\Filament\Resources\Farrowings\Pages\ListFarrowings;
use App\Filament\Resources\Farrowings\Schemas\FarrowingForm;
use App\Filament\Resources\Farrowings\Tables\FarrowingsTable;
use App\Models\Farrowing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FarrowingResource extends Resource
{
    protected static ?string $model = Farrowing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?string $navigationLabel = 'Farrowings';

    protected static string|\UnitEnum|null $navigationGroup = 'Breeding';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return FarrowingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FarrowingsTable::configure($table);
    }

    /**
     * Farrowings must be created from a breeding via the "Record farrowing"
     * action so the litter row is always generated alongside it.
     */
    public static function canCreate(): bool
    {
        return false;
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
            'index' => ListFarrowings::route('/'),
            'edit' => EditFarrowing::route('/{record}/edit'),
        ];
    }
}
