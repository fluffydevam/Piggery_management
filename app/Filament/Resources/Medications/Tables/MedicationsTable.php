<?php

namespace App\Filament\Resources\Medications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MedicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'vaccine' => 'success',
                        'vitamin' => 'info',
                        'dewormer' => 'warning',
                        'antibiotic' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('default_dosage')
                    ->label('Dosage')
                    ->placeholder('—'),

                TextColumn::make('withdrawal_days')
                    ->label('Withdrawal')
                    ->getStateUsing(fn ($record): ?string => $record->withdrawal_days !== null
                        ? $record->withdrawal_days.' days'
                        : null)
                    ->toggleable(),

                TextColumn::make('medical_logs_count')
                    ->label('Times used')
                    ->counts('medicalLogs')
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'vaccine' => 'Vaccine',
                        'vitamin' => 'Vitamin',
                        'dewormer' => 'Dewormer',
                        'antibiotic' => 'Antibiotic',
                        'treatment' => 'Treatment',
                        'supplement' => 'Supplement',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
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
