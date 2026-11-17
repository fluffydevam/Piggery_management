<?php

namespace App\Filament\Resources\MedicalLogs\Tables;

use App\Models\MedicalLog;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MedicalLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date_administered', 'desc')
            ->columns([
                TextColumn::make('subject_label')
                    ->label('Subject')
                    ->getStateUsing(fn (MedicalLog $record): string => $record->subject_label)
                    ->weight('bold'),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        MedicalLog::TYPE_VACCINE => 'success',
                        MedicalLog::TYPE_VITAMIN => 'info',
                        MedicalLog::TYPE_DEWORMER => 'warning',
                        MedicalLog::TYPE_TREATMENT => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('medication.name')
                    ->label('Medication')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('date_administered')
                    ->label('Given')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('next_due_date')
                    ->label('Next due')
                    ->date('M j, Y')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('schedule_status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function (MedicalLog $record): string {
                        if ($record->completed_at !== null) {
                            return 'completed';
                        }

                        if ($record->isOverdue()) {
                            return 'overdue';
                        }

                        return $record->next_due_date !== null ? 'due' : 'given';
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'Completed',
                        'overdue' => 'Overdue',
                        'due' => 'Scheduled',
                        default => 'Given',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'gray',
                        'overdue' => 'danger',
                        'due' => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('dosage')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('administered_by')
                    ->label('By')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        MedicalLog::TYPE_VACCINE => 'Vaccine',
                        MedicalLog::TYPE_VITAMIN => 'Vitamin',
                        MedicalLog::TYPE_DEWORMER => 'Dewormer',
                        MedicalLog::TYPE_TREATMENT => 'Treatment',
                        MedicalLog::TYPE_OTHER => 'Other',
                    ]),
                SelectFilter::make('treatable_type')
                    ->label('Subject type')
                    ->options([
                        \App\Models\Pig::class => 'Pigs',
                        \App\Models\Litter::class => 'Litters',
                    ]),
                SelectFilter::make('schedule')
                    ->label('Schedule')
                    ->options([
                        'due' => 'Due in the next 7 days',
                        'overdue' => 'Overdue',
                        'completed' => 'Completed',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'due' => $query->dueWithin(7),
                            'overdue' => $query->overdue(),
                            'completed' => $query->whereNotNull('completed_at'),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                Action::make('markAdministered')
                    ->label('Mark given')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (MedicalLog $record): bool => $record->completed_at === null
                        && $record->next_due_date !== null)
                    ->requiresConfirmation()
                    ->action(function (MedicalLog $record): void {
                        $record->markAdministered();

                        Notification::make()
                            ->title('Marked as administered')
                            ->success()
                            ->send();
                    }),

                Action::make('administerBooster')
                    ->label('Give booster')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (MedicalLog $record): bool => $record->completed_at === null
                        && $record->next_due_date !== null)
                    ->schema([
                        DatePicker::make('next_due_date')
                            ->label('Next booster due')
                            ->native(false),
                    ])
                    ->action(function (MedicalLog $record, array $data): void {
                        $record->administerBooster($data['next_due_date'] ?? null);

                        Notification::make()
                            ->title('Booster recorded')
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
