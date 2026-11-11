<?php

namespace App\Filament\Resources\Breedings\Tables;

use App\Models\Breeding;
use App\Models\Cage;
use App\Models\Farrowing;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BreedingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('expected_farrowing_date')
            ->columns([
                TextColumn::make('sow.tag_number')
                    ->label('Sow')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('boar.tag_number')
                    ->label('Boar / AI')
                    ->placeholder('AI')
                    ->formatStateUsing(fn (?string $state, Breeding $record): string => $state ?? ($record->semen_source ? 'AI' : '—')),

                TextColumn::make('breeding_date')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('expected_farrowing_date')
                    ->label('Expected farrowing')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('days_until_farrowing')
                    ->label('Due in')
                    ->badge()
                    ->getStateUsing(fn (Breeding $record): ?int => $record->days_until_farrowing)
                    ->formatStateUsing(function (?int $state): string {
                        if ($state === null) {
                            return '—';
                        }

                        if ($state < 0) {
                            return abs($state).'d overdue';
                        }

                        return $state === 0 ? 'Today' : $state.'d';
                    })
                    ->color(function (?int $state, Breeding $record): string {
                        if (! $record->isPregnant()) {
                            return 'gray';
                        }

                        if ($state !== null && $state < 0) {
                            return 'danger';
                        }

                        return $state !== null && $state <= 7 ? 'warning' : 'success';
                    }),

                TextColumn::make('pregnancy_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        Breeding::STATUS_PENDING => 'warning',
                        Breeding::STATUS_CONFIRMED => 'success',
                        Breeding::STATUS_NOT_PREGNANT => 'gray',
                        Breeding::STATUS_ABORTED => 'danger',
                        Breeding::STATUS_FARROWED => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('method')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'ai' ? 'AI' : 'Natural')
                    ->color(fn (string $state): string => $state === 'ai' ? 'info' : 'gray')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('pregnancy_status')
                    ->label('Status')
                    ->options([
                        Breeding::STATUS_PENDING => 'Pending',
                        Breeding::STATUS_CONFIRMED => 'Confirmed pregnant',
                        Breeding::STATUS_NOT_PREGNANT => 'Not pregnant',
                        Breeding::STATUS_ABORTED => 'Aborted',
                        Breeding::STATUS_FARROWED => 'Farrowed',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('recordFarrowing')
                    ->label('Record farrowing')
                    ->icon('heroicon-o-heart')
                    ->color('success')
                    ->visible(fn (Breeding $record): bool => $record->isPregnant() && ! $record->farrowing()->exists())
                    ->schema([
                        DatePicker::make('farrowing_date')
                            ->default(fn (Breeding $record) => $record->expected_farrowing_date)
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
                        Select::make('cage_id')
                            ->label('Farrowing cage')
                            ->options(fn (): array => Cage::query()
                                ->where('type', Cage::TYPE_FARROWING)
                                ->orderBy('cage_number')
                                ->pluck('cage_number', 'id')
                                ->all())
                            ->searchable()
                            ->native(false),
                        Textarea::make('notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->action(function (Breeding $record, array $data): void {
                        /** @var Farrowing $farrowing */
                        $farrowing = $record->recordFarrowing($data);

                        Notification::make()
                            ->title('Farrowing recorded')
                            ->body("{$farrowing->born_alive} born alive, {$farrowing->stillborn} stillborn.")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
