<?php

namespace App\Filament\Resources\MedicalLogs\Schemas;

use App\Models\Litter;
use App\Models\MedicalLog;
use App\Models\Medication;
use App\Models\Pig;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class MedicalLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Subject')
                    ->columns(2)
                    ->schema([
                        Select::make('treatable_type')
                            ->label('Treat')
                            ->options([
                                Pig::class => 'A pig (sow / boar / gilt)',
                                Litter::class => 'A litter',
                            ])
                            ->default(Pig::class)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('treatable_id', null))
                            ->native(false),

                        Select::make('treatable_id')
                            ->label('Animal / litter')
                            ->options(function (Get $get): array {
                                if ($get('treatable_type') === Litter::class) {
                                    return Litter::query()
                                        ->orderBy('litter_tag')
                                        ->get()
                                        ->mapWithKeys(fn (Litter $litter): array => [
                                            $litter->id => ($litter->litter_tag ?? 'Litter #'.$litter->id)
                                                .($litter->sow?->tag_number ? ' — sow '.$litter->sow->tag_number : ''),
                                        ])
                                        ->all();
                                }

                                return Pig::query()
                                    ->orderBy('tag_number')
                                    ->pluck('tag_number', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->required()
                            ->native(false),
                    ]),

                Section::make('Treatment')
                    ->columns(2)
                    ->schema([
                        Select::make('type')
                            ->options([
                                MedicalLog::TYPE_VACCINE => 'Vaccine',
                                MedicalLog::TYPE_VITAMIN => 'Vitamin',
                                MedicalLog::TYPE_DEWORMER => 'Dewormer',
                                MedicalLog::TYPE_TREATMENT => 'Treatment',
                                MedicalLog::TYPE_OTHER => 'Other',
                            ])
                            ->required()
                            ->native(false),

                        Select::make('medication_id')
                            ->label('Medication')
                            ->options(fn (): array => Medication::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->native(false),

                        DatePicker::make('date_administered')
                            ->label('Date given')
                            ->default(today())
                            ->required()
                            ->native(false),

                        TextInput::make('dosage')
                            ->maxLength(50)
                            ->placeholder('e.g. 2 mL'),

                        Select::make('route')
                            ->options([
                                'im' => 'Intramuscular (IM)',
                                'iv' => 'Intravenous (IV)',
                                'sc' => 'Subcutaneous (SC)',
                                'oral' => 'Oral',
                                'topical' => 'Topical',
                            ])
                            ->native(false),

                        TextInput::make('administered_by')
                            ->label('Administered by')
                            ->maxLength(255),

                        DatePicker::make('next_due_date')
                            ->label('Next dose due')
                            ->native(false)
                            ->helperText('Set this to create a booster reminder.'),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
