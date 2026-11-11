<?php

namespace App\Filament\Widgets;

use App\Models\MedicalLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class MedicalScheduleWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Active medical schedules';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MedicalLog::query()
                    ->whereNotNull('next_due_date')
                    ->whereNull('completed_at')
                    ->where('next_due_date', '<=', today()->addDays(7)->toDateString())
                    ->with(['treatable', 'medication'])
                    ->orderBy('next_due_date')
            )
            ->paginated(false)
            ->emptyStateHeading('No boosters due')
            ->emptyStateDescription('Medical schedules due in the next 7 days or overdue will appear here.')
            ->emptyStateIcon('heroicon-o-shield-check')
            ->columns([
                TextColumn::make('subject_label')
                    ->label('Subject')
                    ->getStateUsing(fn (MedicalLog $record): string => $record->subject_label)
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('medication.name')
                    ->label('Medication')
                    ->placeholder('—')
                    ->wrap(),

                TextColumn::make('next_due_date')
                    ->label('Due')
                    ->date('M j')
                    ->badge()
                    ->color(fn (MedicalLog $record): string => $record->isOverdue() ? 'danger' : 'warning'),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ]);
    }
}
