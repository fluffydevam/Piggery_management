<?php

namespace App\Filament\Resources\Litters\Schemas;

use App\Models\Cage;
use App\Models\Litter;
use App\Rules\OwnedByCurrentUser;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LitterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Litter')
                    ->columns(2)
                    ->schema([
                        TextInput::make('litter_tag')
                            ->label('Litter tag')
                            ->maxLength(50),

                        Select::make('status')
                            ->options([
                                Litter::STATUS_NURSING => 'Nursing',
                                Litter::STATUS_WEANED => 'Weaned',
                                Litter::STATUS_SOLD => 'Sold',
                                Litter::STATUS_FINISHED => 'Finished',
                                Litter::STATUS_LOST => 'Lost',
                            ])
                            ->default(Litter::STATUS_NURSING)
                            ->required()
                            ->native(false),

                        TextInput::make('current_count')
                            ->label('Piglets alive')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(255)
                            ->required(),

                        TextInput::make('deaths_count')
                            ->label('Deaths to date')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(255)
                            ->default(0),

                        Select::make('cage_id')
                            ->label('Cage')
                            ->options(fn (): array => Cage::query()
                                ->orderBy('cage_number')
                                ->pluck('cage_number', 'id')
                                ->all())
                            ->searchable()
                            ->native(false)
                            ->rules([new OwnedByCurrentUser(Cage::class)]),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Weaning')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('weaned_date')
                            ->native(false),

                        TextInput::make('weaned_count')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(255),
                    ]),
            ]);
    }
}
