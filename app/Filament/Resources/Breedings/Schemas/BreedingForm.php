<?php

namespace App\Filament\Resources\Breedings\Schemas;

use App\Models\Breeding;
use App\Models\Pig;
use App\Rules\BoarGender;
use App\Rules\OneActiveBreedingPerSow;
use App\Rules\SowGender;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class BreedingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mating')
                    ->columns(2)
                    ->schema([
                        Select::make('sow_id')
                            ->label('Sow')
                            ->relationship(
                                name: 'sow',
                                titleAttribute: 'tag_number',
                                modifyQueryUsing: fn ($query) => $query
                                    ->whereIn('type', [Pig::TYPE_SOW, Pig::TYPE_GILT])
                                    ->where('sex', 'female')
                                    ->where('status', Pig::STATUS_ACTIVE)
                                    ->orderBy('tag_number'),
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->rules(fn (?Breeding $record): array => [
                                new SowGender(),
                                new OneActiveBreedingPerSow($record?->id),
                            ]),

                        Select::make('boar_id')
                            ->label('Boar')
                            ->relationship(
                                name: 'boar',
                                titleAttribute: 'tag_number',
                                modifyQueryUsing: fn ($query) => $query
                                    ->where('type', Pig::TYPE_BOAR)
                                    ->where('sex', 'male')
                                    ->where('status', Pig::STATUS_ACTIVE)
                                    ->orderBy('tag_number'),
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->rules([new BoarGender()])
                            ->helperText('Leave empty for artificial insemination.'),

                        DatePicker::make('breeding_date')
                            ->required()
                            ->maxDate(today())
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                $set(
                                    'expected_farrowing_date',
                                    $state
                                        ? \Illuminate\Support\Carbon::parse($state)
                                            ->addDays(Breeding::GESTATION_DAYS)
                                            ->toDateString()
                                        : null,
                                );
                            }),

                        DatePicker::make('expected_farrowing_date')
                            ->label('Expected farrowing')
                            ->readOnly()
                            ->dehydrated()
                            ->helperText('Auto-calculated: breeding date + '.Breeding::GESTATION_DAYS.' days.'),

                        Select::make('method')
                            ->options([
                                'natural' => 'Natural service',
                                'ai' => 'Artificial insemination',
                            ])
                            ->default('natural')
                            ->required()
                            ->live()
                            ->native(false),

                        TextInput::make('semen_source')
                            ->label('Semen source / batch')
                            ->maxLength(255)
                            ->visible(fn (callable $get): bool => $get('method') === 'ai'),
                    ]),

                Section::make('Pregnancy')
                    ->columns(2)
                    ->schema([
                        Select::make('pregnancy_status')
                            ->options([
                                Breeding::STATUS_PENDING => 'Pending',
                                Breeding::STATUS_CONFIRMED => 'Confirmed pregnant',
                                Breeding::STATUS_NOT_PREGNANT => 'Not pregnant',
                                Breeding::STATUS_ABORTED => 'Aborted',
                                Breeding::STATUS_FARROWED => 'Farrowed',
                            ])
                            ->default(Breeding::STATUS_PENDING)
                            ->required()
                            ->native(false),

                        DatePicker::make('confirmed_pregnant_at')
                            ->label('Pregnancy confirmed on')
                            ->native(false),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
