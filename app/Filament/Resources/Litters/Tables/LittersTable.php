<?php

namespace App\Filament\Resources\Litters\Tables;

use App\Models\Cage;
use App\Models\Litter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LittersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('litter_tag')
                    ->label('Litter')
                    ->searchable()
                    ->weight('bold')
                    ->placeholder('—'),

                TextColumn::make('sow_tag')
                    ->label('Sow')
                    ->getStateUsing(fn (Litter $record): ?string => $record->sow?->tag_number)
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        Litter::STATUS_NURSING => 'success',
                        Litter::STATUS_WEANED => 'info',
                        Litter::STATUS_SOLD => 'warning',
                        Litter::STATUS_FINISHED => 'gray',
                        Litter::STATUS_LOST => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('current_count')
                    ->label('Alive')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('deaths_count')
                    ->label('Deaths')
                    ->badge()
                    ->color('danger')
                    ->sortable(),

                TextColumn::make('farrowing.farrowing_date')
                    ->label('Born')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('days_old')
                    ->label('Age')
                    ->getStateUsing(fn (Litter $record): ?string => $record->daysOld() !== null
                        ? $record->daysOld().'d'
                        : null)
                    ->badge()
                    ->color(fn (?string $state): string => $state !== null && (int) $state >= Litter::WEANING_AGE_DAYS
                        ? 'warning'
                        : 'gray'),

                TextColumn::make('cage.cage_number')
                    ->label('Cage')
                    ->badge()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Litter::STATUS_NURSING => 'Nursing',
                        Litter::STATUS_WEANED => 'Weaned',
                        Litter::STATUS_SOLD => 'Sold',
                        Litter::STATUS_FINISHED => 'Finished',
                        Litter::STATUS_LOST => 'Lost',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('recordDeath')
                    ->label('Record death')
                    ->icon('heroicon-o-minus-circle')
                    ->color('danger')
                    ->visible(fn (Litter $record): bool => $record->current_count > 0)
                    ->schema([
                        TextInput::make('count')
                            ->label('Number of piglets lost')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(fn (Litter $record): int => $record->current_count)
                            ->default(1)
                            ->required(),
                    ])
                    ->action(function (Litter $record, array $data): void {
                        $record->recordDeath((int) $data['count']);

                        Notification::make()
                            ->title('Death recorded')
                            ->body("{$record->fresh()->current_count} piglets remain.")
                            ->warning()
                            ->send();
                    }),

                Action::make('wean')
                    ->label('Wean litter')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn (Litter $record): bool => $record->status === Litter::STATUS_NURSING)
                    ->schema([
                        TextInput::make('weaned_count')
                            ->label('Piglets weaned')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(fn (Litter $record): int => $record->current_count)
                            ->required(),
                    ])
                    ->action(function (Litter $record, array $data): void {
                        $record->wean(today()->toDateString(), (int) $data['weaned_count']);

                        Notification::make()
                            ->title('Litter weaned')
                            ->success()
                            ->send();
                    }),

                Action::make('promoteToGilt')
                    ->label('Keep as gilt')
                    ->icon('heroicon-o-sparkles')
                    ->color('success')
                    ->schema([
                        TextInput::make('tag_number')
                            ->label('New gilt tag')
                            ->required()
                            ->maxLength(50)
                            ->unique(table: 'pigs', column: 'tag_number'),
                        Select::make('cage_id')
                            ->label('Cage')
                            ->options(fn (): array => Cage::query()
                                ->orderBy('cage_number')
                                ->pluck('cage_number', 'id')
                                ->all())
                            ->searchable()
                            ->native(false),
                        Textarea::make('notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->action(function (Litter $record, array $data): void {
                        $gilt = $record->promoteToGilt($data['tag_number'], [
                            'cage_id' => $data['cage_id'] ?? null,
                            'notes' => $data['notes'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Gilt added to the herd')
                            ->body("{$gilt->tag_number} is now an active gilt.")
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
