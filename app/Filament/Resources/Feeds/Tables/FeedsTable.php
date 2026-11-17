<?php

namespace App\Filament\Resources\Feeds\Tables;

use App\Models\Feed;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class FeedsTable
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

                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color('info')
                    ->sortable(),

                TextColumn::make('stock_kg')
                    ->label('Stock')
                    ->getStateUsing(fn (Feed $record): string => number_format((float) $record->stock_kg, 2).' kg')
                    ->badge()
                    ->color(fn (Feed $record): string => $record->isLowStock() ? 'danger' : 'success')
                    ->sortable(),

                TextColumn::make('stock_in_bags')
                    ->label('Bags')
                    ->getStateUsing(fn (Feed $record): string => number_format($record->stock_in_bags, 2))
                    ->toggleable(),

                TextColumn::make('reorder_level_kg')
                    ->label('Reorder at')
                    ->getStateUsing(fn (Feed $record): string => number_format((float) $record->reorder_level_kg, 2).' kg')
                    ->toggleable(),

                TextColumn::make('unit_cost')
                    ->label('Cost/bag')
                    ->money('PHP')
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Last updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'starter' => 'Starter',
                        'grower' => 'Grower',
                        'finisher' => 'Finisher',
                        'gestating' => 'Gestating',
                        'lactating' => 'Lactating',
                        'boar' => 'Boar',
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
