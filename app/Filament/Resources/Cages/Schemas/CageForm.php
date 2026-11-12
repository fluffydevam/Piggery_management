<?php

namespace App\Filament\Resources\Cages\Schemas;

use App\Models\Cage;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class CageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cage details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('cage_number')
                            ->label('Cage number')
                            ->required()
                            ->maxLength(50)
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule) => $rule->where('user_id', auth()->id()),
                            ),

                        Select::make('type')
                            ->options([
                                Cage::TYPE_GESTATION => 'Gestation',
                                Cage::TYPE_FARROWING => 'Farrowing',
                                Cage::TYPE_BOAR => 'Boar pen',
                                Cage::TYPE_WEANING => 'Weaning',
                                Cage::TYPE_GROWER => 'Grower',
                                Cage::TYPE_QUARANTINE => 'Quarantine',
                            ])
                            ->required()
                            ->native(false),

                        TextInput::make('capacity')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(255)
                            ->default(1)
                            ->required()
                            ->helperText('Maximum number of pigs housed here.'),

                        Select::make('status')
                            ->options([
                                Cage::STATUS_AVAILABLE => 'Available',
                                Cage::STATUS_OCCUPIED => 'Occupied',
                                Cage::STATUS_MAINTENANCE => 'Under maintenance',
                                Cage::STATUS_INACTIVE => 'Inactive',
                            ])
                            ->default(Cage::STATUS_AVAILABLE)
                            ->required()
                            ->native(false),

                        TextInput::make('location')
                            ->maxLength(255)
                            ->placeholder('e.g. Barn A'),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
