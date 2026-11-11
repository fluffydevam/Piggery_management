<?php

namespace App\Filament\Resources\Medications\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MedicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Medication')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),

                        Select::make('type')
                            ->options([
                                'vaccine' => 'Vaccine',
                                'vitamin' => 'Vitamin',
                                'dewormer' => 'Dewormer',
                                'antibiotic' => 'Antibiotic',
                                'treatment' => 'Treatment',
                                'supplement' => 'Supplement',
                            ])
                            ->required()
                            ->native(false),

                        TextInput::make('default_dosage')
                            ->label('Default dosage')
                            ->maxLength(50)
                            ->placeholder('e.g. 2 mL'),

                        TextInput::make('withdrawal_days')
                            ->label('Withdrawal period (days)')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->helperText('Days before meat can be sold after use.'),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
