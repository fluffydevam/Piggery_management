<?php

namespace App\Filament\Resources\Pigs\Tables;

use App\Models\Pig;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('tag_number')
            ->columns([
                TextColumn::make('tag_number')
                    ->label('Tag')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        Pig::TYPE_SOW => 'success',
                        Pig::TYPE_BOAR => 'warning',
                        Pig::TYPE_GILT => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('breed')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('sex')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => $state === 'female' ? 'info' : 'warning'),

                TextColumn::make('birth_date')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('age_in_months')
                    ->label('Age')
                    ->getStateUsing(fn (Pig $record): ?string => $record->age_in_months !== null
                        ? $record->age_in_months.' mo'
                        : null)
                    ->toggleable(),

                TextColumn::make('cage.cage_number')
                    ->label('Cage')
                    ->badge()
                    ->placeholder('Unassigned')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        Pig::STATUS_ACTIVE => 'success',
                        Pig::STATUS_SOLD => 'info',
                        Pig::STATUS_CULLED => 'warning',
                        Pig::STATUS_DECEASED => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        Pig::TYPE_SOW => 'Sow',
                        Pig::TYPE_BOAR => 'Boar',
                        Pig::TYPE_GILT => 'Gilt',
                        Pig::TYPE_PIGLET => 'Piglet',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        Pig::STATUS_ACTIVE => 'Active',
                        Pig::STATUS_SOLD => 'Sold',
                        Pig::STATUS_CULLED => 'Culled',
                        Pig::STATUS_DECEASED => 'Deceased',
                    ]),
                SelectFilter::make('cage')
                    ->relationship('cage', 'cage_number')
                    ->searchable()
                    ->preload(),
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
