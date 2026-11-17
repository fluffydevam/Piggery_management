<?php

namespace App\Filament\Widgets;

use App\Models\Breeding;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingFarrowings extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Upcoming farrowings (next 7 days)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Breeding::query()
                    ->upcomingFarrowing(7)
                    ->with(['sow', 'boar'])
                    ->orderBy('expected_farrowing_date')
            )
            ->paginated(false)
            ->emptyStateHeading('No farrowings due this week')
            ->emptyStateDescription('Pregnancies expected to farrow in the next 7 days will appear here.')
            ->emptyStateIcon('heroicon-o-heart')
            ->columns([
                TextColumn::make('sow.tag_number')
                    ->label('Sow')
                    ->weight('bold'),

                TextColumn::make('boar.tag_number')
                    ->label('Boar')
                    ->placeholder('AI'),

                TextColumn::make('breeding_date')
                    ->date('M j, Y'),

                TextColumn::make('expected_farrowing_date')
                    ->label('Expected')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('days_until_farrowing')
                    ->label('Due in')
                    ->badge()
                    ->getStateUsing(fn (Breeding $record): ?int => $record->days_until_farrowing)
                    ->formatStateUsing(fn (?int $state): string => match (true) {
                        $state === null => '—',
                        $state === 0 => 'Today',
                        $state < 0 => abs($state).'d overdue',
                        default => $state.'d',
                    })
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state < 0 => 'danger',
                        $state <= 3 => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('pregnancy_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),
            ]);
    }
}
