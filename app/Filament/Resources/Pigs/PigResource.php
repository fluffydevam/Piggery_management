<?php

namespace App\Filament\Resources\Pigs;

use App\Filament\Resources\Pigs\Pages\CreatePig;
use App\Filament\Resources\Pigs\Pages\EditPig;
use App\Filament\Resources\Pigs\Pages\ListPigs;
use App\Filament\Resources\Pigs\Schemas\PigForm;
use App\Filament\Resources\Pigs\Tables\PigsTable;
use App\Models\Pig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PigResource extends Resource
{
    protected static ?string $model = Pig::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Pigs';

    protected static string|\UnitEnum|null $navigationGroup = 'Herd';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'tag_number';

    public static function form(Schema $schema): Schema
    {
        return PigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PigsTable::configure($table);
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
            'index' => ListPigs::route('/'),
            'create' => CreatePig::route('/create'),
            'edit' => EditPig::route('/{record}/edit'),
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
