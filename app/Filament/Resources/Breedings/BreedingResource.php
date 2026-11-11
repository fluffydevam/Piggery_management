<?php

namespace App\Filament\Resources\Breedings;

use App\Filament\Resources\Breedings\Pages\CreateBreeding;
use App\Filament\Resources\Breedings\Pages\EditBreeding;
use App\Filament\Resources\Breedings\Pages\ListBreedings;
use App\Filament\Resources\Breedings\Schemas\BreedingForm;
use App\Filament\Resources\Breedings\Tables\BreedingsTable;
use App\Models\Breeding;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BreedingResource extends Resource
{
    protected static ?string $model = Breeding::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?string $navigationLabel = 'Breedings';

    protected static string|\UnitEnum|null $navigationGroup = 'Breeding';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return BreedingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BreedingsTable::configure($table);
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
            'index' => ListBreedings::route('/'),
            'create' => CreateBreeding::route('/create'),
            'edit' => EditBreeding::route('/{record}/edit'),
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
