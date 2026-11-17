<?php

namespace App\Filament\Resources\FeedTransactions\Tables;

use App\Models\FeedTransaction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FeedTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('transaction_date', 'desc')
            ->columns([
                TextColumn::make('transaction_date')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('feed.name')
                    ->label('Feed')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        FeedTransaction::TYPE_IN => 'Stock in',
                        FeedTransaction::TYPE_OUT => 'Stock out',
                        default => 'Adjustment',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        FeedTransaction::TYPE_IN => 'success',
                        FeedTransaction::TYPE_OUT => 'warning',
                        default => 'info',
                    })
                    ->sortable(),

                TextColumn::make('quantity_kg')
                    ->label('Quantity')
                    ->getStateUsing(fn (FeedTransaction $record): string => number_format($record->signedQuantity(), 2).' kg')
                    ->badge()
                    ->color(fn (FeedTransaction $record): string => $record->signedQuantity() < 0 ? 'danger' : 'success')
                    ->sortable(),

                TextColumn::make('reference')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('cost')
                    ->money('PHP')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user.name')
                    ->label('Recorded by')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('notes')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('feed')
                    ->relationship('feed', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->options([
                        FeedTransaction::TYPE_IN => 'Stock in',
                        FeedTransaction::TYPE_OUT => 'Stock out',
                        FeedTransaction::TYPE_ADJUSTMENT => 'Adjustment',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
