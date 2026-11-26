<?php

namespace App\Filament\Resources\Feeds\Schemas;

use App\Models\Feed;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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
                            ->live()
                            ->helperText('One bag of this feed weighs this many kg.'),

                        TextInput::make('reorder_level_bags')
                            ->label('Reorder level (bags)')
                            ->numeric()
                            ->minValue(0)
                            ->default(1)
                            ->required()
                            ->live()
                            ->helperText(fn (Get $get): string => sprintf(
                                'Alert threshold: %.2f bag(s) = %.2f kg.',
                                (float) ($get('reorder_level_bags') ?? 0),
                                round((float) ($get('reorder_level_bags') ?? 0) * (float) ($get('bag_weight_kg') ?? 0), 2),
                            )),

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
                    ->description('Stock is calculated automatically from feed transactions recorded in bags.')
                    ->schema([
                        TextInput::make('stock_kg')
                            ->label('Stock')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn (?Feed $record): string => $record
                                ? $record->stockLabel()
                                : '0.00 bags (0.00 kg)'),
                    ]),
            ]);
    }
}
