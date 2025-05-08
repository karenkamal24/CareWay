<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\Chart;
use App\Filament\Widgets\OrdersStatsOverview;
use App\Filament\Widgets\orderstuts;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $routePath = '/';

    public function getHeading(): string
    {
        return 'Dashboard';
    }

    public function getWidgets(): array
    {
        return [
            OrdersStatsOverview::class,
            Chart::class,
            orderstuts::class,
        ];
    }
}