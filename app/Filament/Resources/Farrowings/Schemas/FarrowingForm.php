<?php

namespace App\Filament\Resources\Farrowings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FarrowingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Delivery outcome')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('farrowing_date')
                            ->required()
                            ->native(false),

                        TextInput::make('born_alive')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(255)
                            ->default(0)
                            ->required(),

                        TextInput::make('stillborn')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(255)
                            ->default(0)
                            ->required(),

                        TextInput::make('mummified')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(255)
                            ->default(0)
                            ->required(),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
