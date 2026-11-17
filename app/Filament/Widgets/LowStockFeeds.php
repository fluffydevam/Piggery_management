<?php

namespace App\Filament\Widgets;

use App\Models\Feed;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LowStockFeeds extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Low feed stock';

    public function table(Table $table): Table
    {
        return $table
            ->query(Feed::query()->lowStock()->orderBy('stock_kg'))
            ->paginated(false)
            ->emptyStateHeading('Feed stock is healthy')
            ->emptyStateDescription('Feeds at or below their reorder level will appear here.')
            ->emptyStateIcon('heroicon-o-archive-box')
            ->columns([
                TextColumn::make('name')
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('stock_kg')
                    ->label('Stock')
                    ->getStateUsing(fn (Feed $record): string => number_format((float) $record->stock_kg, 1).' kg')
                    ->badge()
                    ->color('danger'),

                TextColumn::make('reorder_level_kg')
                    ->label('Reorder at')
                    ->getStateUsing(fn (Feed $record): string => number_format((float) $record->reorder_level_kg, 1).' kg'),
            ]);
    }
}
