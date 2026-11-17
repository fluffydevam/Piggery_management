<?php

namespace App\Filament\Resources\Feeds\Schemas;

use App\Models\Feed;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class FeedForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Feed details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(100)
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule) => $rule->where('user_id', auth()->id()),
                            ),

                        Select::make('category')
                            ->options([
                                'starter' => 'Starter',
                                'grower' => 'Grower',
                                'finisher' => 'Finisher',
                                'gestating' => 'Gestating',
                                'lactating' => 'Lactating',
                                'boar' => 'Boar',
                                'supplement' => 'Supplement',
                            ])
                            ->required()
                            ->native(false),

                        TextInput::make('bag_weight_kg')
                            ->label('Bag weight (kg)')
                            ->numeric()
                            ->minValue(0.01)
                            ->default(50)
                            ->required()
                            ->helperText('Used to show stock in bags.'),

                        TextInput::make('reorder_level_kg')
                            ->label('Reorder level (kg)')
                            ->numeric()
                            ->minValue(0)
                            ->default(100)
                            ->required()
                            ->helperText('You are alerted when stock drops to this level.'),

                        TextInput::make('unit_cost')
                            ->label('Cost per bag')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₱'),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Current stock')
                    ->description('Stock is calculated automatically from feed transactions and cannot be edited directly.')
                    ->schema([
                        TextInput::make('stock_kg')
                            ->label('Stock (kg)')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn (?Feed $record): ?string => $record
                                ? number_format((float) $record->stock_kg, 2).' kg ('.number_format($record->stock_in_bags, 2).' bags)'
                                : '0.00 kg'),
                    ]),
            ]);
    }
}
