<?php

namespace App\Filament\Resources\FeedTransactions\Schemas;

use App\Models\Feed;
use App\Models\FeedTransaction;
use App\Rules\OwnedByCurrentUser;
use App\Rules\StockAvailability;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class FeedTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transaction')
                    ->columns(2)
                    ->schema([
                        Select::make('feed_id')
                            ->label('Feed')
                            ->relationship('feed', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->native(false)
                            ->rules([new OwnedByCurrentUser(Feed::class)]),

                        Select::make('type')
                            ->options([
                                FeedTransaction::TYPE_IN => 'Stock in',
                                FeedTransaction::TYPE_OUT => 'Stock out',
                                FeedTransaction::TYPE_ADJUSTMENT => 'Adjustment',
                            ])
                            ->default(FeedTransaction::TYPE_IN)
                            ->required()
                            ->live()
                            ->native(false)
                            ->helperText('Adjustments accept negative values (e.g. -10 for spoilage).'),

                        TextInput::make('quantity_kg')
                            ->label('Quantity (kg)')
                            ->numeric()
                            ->required()
                            ->rules(fn (Get $get, ?FeedTransaction $record): array => [
                                new StockAvailability(
                                    feedId: $get('feed_id'),
                                    type: (string) $get('type'),
                                    ignoreTransactionId: $record?->id,
                                ),
                            ])
                            ->helperText(function (Get $get): ?string {
                                $feed = Feed::find($get('feed_id'));

                                if (! $feed instanceof Feed) {
                                    return null;
                                }

                                return 'Available: '.number_format((float) $feed->stock_kg, 2).' kg ('
                                    .number_format($feed->stock_in_bags, 2).' bags)';
                            }),

                        DatePicker::make('transaction_date')
                            ->required()
                            ->default(today())
                            ->maxDate(today())
                            ->native(false),

                        TextInput::make('reference')
                            ->maxLength(255)
                            ->placeholder('e.g. PO-1001 / weekly feeding'),

                        TextInput::make('cost')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₱'),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
