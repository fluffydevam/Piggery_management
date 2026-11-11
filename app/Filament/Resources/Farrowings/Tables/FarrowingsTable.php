<?php

namespace App\Filament\Resources\Farrowings\Tables;

use App\Models\Farrowing;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FarrowingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('farrowing_date', 'desc')
            ->columns([
                TextColumn::make('breeding.sow.tag_number')
                    ->label('Sow')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('farrowing_date')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('born_alive')
                    ->label('Born alive')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('stillborn')
                    ->badge()
                    ->color('danger')
                    ->sortable(),

                TextColumn::make('mummified')
                    ->badge()
                    ->color('warning')
                    ->sortable(),

                TextColumn::make('total_born')
                    ->label('Total born')
                    ->getStateUsing(fn (Farrowing $record): int => $record->total_born),

                TextColumn::make('survival_rate')
                    ->label('Survival')
                    ->getStateUsing(fn (Farrowing $record): ?string => $record->survival_rate !== null
                        ? $record->survival_rate.'%'
                        : null)
                    ->badge()
                    ->color(fn (?string $state): string => $state === null
                        ? 'gray'
                        : ((float) $state >= 90 ? 'success' : ((float) $state >= 75 ? 'warning' : 'danger'))),

                TextColumn::make('notes')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
