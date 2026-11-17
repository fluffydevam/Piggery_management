<?php

namespace App\Filament\Resources\FeedTransactions;

use App\Filament\Resources\FeedTransactions\Pages\CreateFeedTransaction;
use App\Filament\Resources\FeedTransactions\Pages\EditFeedTransaction;
use App\Filament\Resources\FeedTransactions\Pages\ListFeedTransactions;
use App\Filament\Resources\FeedTransactions\Schemas\FeedTransactionForm;
use App\Filament\Resources\FeedTransactions\Tables\FeedTransactionsTable;
use App\Models\FeedTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FeedTransactionResource extends Resource
{
    protected static ?string $model = FeedTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $navigationLabel = 'Feed transactions';

    protected static string|\UnitEnum|null $navigationGroup = 'Feed';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return FeedTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeedTransactionsTable::configure($table);
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
            'index' => ListFeedTransactions::route('/'),
            'create' => CreateFeedTransaction::route('/create'),
            'edit' => EditFeedTransaction::route('/{record}/edit'),
        ];
    }
}
