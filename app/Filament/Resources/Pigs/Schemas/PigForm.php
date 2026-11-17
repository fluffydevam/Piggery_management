<?php

namespace App\Filament\Resources\Pigs\Schemas;

use App\Models\Cage;
use App\Models\Pig;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identity')
                    ->columns(2)
                    ->schema([
                        TextInput::make('tag_number')
                            ->label('Tag number')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),

                        Select::make('type')
                            ->options([
                                Pig::TYPE_SOW => 'Sow',
                                Pig::TYPE_BOAR => 'Boar',
                                Pig::TYPE_GILT => 'Gilt',
                                Pig::TYPE_PIGLET => 'Piglet',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (?string $state, callable $set): void {
                                if ($state === Pig::TYPE_BOAR) {
                                    $set('sex', 'male');
                                } elseif (in_array($state, [Pig::TYPE_SOW, Pig::TYPE_GILT], true)) {
                                    $set('sex', 'female');
                                }
                            })
                            ->native(false),

                        Select::make('sex')
                            ->options([
                                'female' => 'Female',
                                'male' => 'Male',
                            ])
                            ->required()
                            ->native(false),

                        TextInput::make('breed')
                            ->maxLength(100)
                            ->placeholder('e.g. Landrace'),

                        DatePicker::make('birth_date')
                            ->maxDate(today())
                            ->native(false),

                        Select::make('cage_id')
                            ->label('Cage')
                            ->relationship(
                                name: 'cage',
                                titleAttribute: 'cage_number',
                                modifyQueryUsing: fn ($query) => $query->orderBy('cage_number'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (Cage $record): string => $record->cage_number.' ('.ucfirst($record->type).')'
                            )
                            ->searchable()
                            ->preload()
                            ->native(false),
                    ]),

                Section::make('Status & origin')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->options([
                                Pig::STATUS_ACTIVE => 'Active',
                                Pig::STATUS_SOLD => 'Sold',
                                Pig::STATUS_CULLED => 'Culled',
                                Pig::STATUS_DECEASED => 'Deceased',
                            ])
                            ->default(Pig::STATUS_ACTIVE)
                            ->required()
                            ->native(false),

                        TextInput::make('origin')
                            ->maxLength(255)
                            ->placeholder('bred_on_farm / purchased'),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
