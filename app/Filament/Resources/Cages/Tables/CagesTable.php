<?php

namespace App\Filament\Resources\Cages\Tables;

use App\Models\Cage;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('cage_number')
            ->columns([
                TextColumn::make('cage_number')
                    ->label('Cage')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        Cage::TYPE_GESTATION => 'info',
                        Cage::TYPE_FARROWING => 'success',
                        Cage::TYPE_BOAR => 'warning',
                        Cage::TYPE_QUARANTINE => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('occupancy')
                    ->label('Occupancy')
                    ->getStateUsing(fn (Cage $record): string => $record->occupancyCount().' / '.$record->capacity)
                    ->badge()
                    ->color(fn (Cage $record): string => $record->isFull() ? 'danger' : 'success'),

                TextColumn::make('location')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state)))
                    ->color(fn (string $state): string => match ($state) {
                        Cage::STATUS_AVAILABLE => 'success',
                        Cage::STATUS_OCCUPIED => 'info',
                        Cage::STATUS_MAINTENANCE => 'warning',
                        Cage::STATUS_INACTIVE => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('notes')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        Cage::TYPE_GESTATION => 'Gestation',
                        Cage::TYPE_FARROWING => 'Farrowing',
                        Cage::TYPE_BOAR => 'Boar pen',
                        Cage::TYPE_WEANING => 'Weaning',
                        Cage::TYPE_GROWER => 'Grower',
                        Cage::TYPE_QUARANTINE => 'Quarantine',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        Cage::STATUS_AVAILABLE => 'Available',
                        Cage::STATUS_OCCUPIED => 'Occupied',
                        Cage::STATUS_MAINTENANCE => 'Maintenance',
                        Cage::STATUS_INACTIVE => 'Inactive',
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
