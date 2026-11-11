<?php

namespace App\Filament\Widgets;

use App\Models\Breeding;
use App\Models\Feed;
use App\Models\Litter;
use App\Models\MedicalLog;
use App\Models\Pig;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FarmStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $sows = Pig::query()->active()->sows()->count();
        $boars = Pig::query()->active()->boars()->count();
        $pregnant = Breeding::query()->pregnant()->count();
        $nursing = Litter::query()->nursing()->count();
        $nursingPiglets = (int) Litter::query()->nursing()->sum('current_count');

        $dueThisWeek = Breeding::query()->upcomingFarrowing(7)->count();
        $overdue = Breeding::query()->overdueFarrowing()->count();
        $lowStock = Feed::query()->lowStock()->count();
        $dueMedical = MedicalLog::query()->dueWithin(7)->count();
        $overdueMedical = MedicalLog::query()->overdue()->count();

        return [
            Stat::make('Active sows', (string) $sows)
                ->description("{$pregnant} pregnant · {$nursing} nursing litters")
                ->descriptionIcon('heroicon-m-heart')
                ->color('success'),

            Stat::make('Active boars', (string) $boars)
                ->description('Available for service')
                ->descriptionIcon('heroicon-m-user')
                ->color('info'),

            Stat::make('Farrowings due (7 days)', (string) $dueThisWeek)
                ->description($overdue > 0 ? "{$overdue} overdue" : 'Nothing overdue')
                ->descriptionIcon($overdue > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($overdue > 0 ? 'danger' : ($dueThisWeek > 0 ? 'warning' : 'gray')),

            Stat::make('Piglets nursing', (string) $nursingPiglets)
                ->description("Across {$nursing} litters")
                ->descriptionIcon('heroicon-m-square-3-stack-3d')
                ->color('info'),

            Stat::make('Low-stock feeds', (string) $lowStock)
                ->description($lowStock > 0 ? 'Reorder needed' : 'All feeds healthy')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color($lowStock > 0 ? 'danger' : 'success'),

            Stat::make('Medical due (7 days)', (string) $dueMedical)
                ->description($overdueMedical > 0 ? "{$overdueMedical} overdue" : 'All boosters on track')
                ->descriptionIcon($overdueMedical > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-shield-check')
                ->color($overdueMedical > 0 ? 'danger' : ($dueMedical > 0 ? 'warning' : 'gray')),
        ];
    }
}
