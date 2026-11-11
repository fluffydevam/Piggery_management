<?php

namespace App\Filament\Resources\Litters;

use App\Filament\Resources\Litters\Pages\EditLitter;
use App\Filament\Resources\Litters\Pages\ListLitters;
use App\Filament\Resources\Litters\Schemas\LitterForm;
use App\Filament\Resources\Litters\Tables\LittersTable;
use App\Models\Litter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LitterResource extends Resource
{
    protected static ?string $model = Litter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquare3Stack3d;

    protected static ?string $navigationLabel = 'Litters';

    protected static string|\UnitEnum|null $navigationGroup = 'Breeding';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return LitterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LittersTable::configure($table);
    }

    /**
     * A litter is always created together with its farrowing, so it cannot be
     * created manually from this resource.
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
            'index' => ListLitters::route('/'),
            'edit' => EditLitter::route('/{record}/edit'),
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
