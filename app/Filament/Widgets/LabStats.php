<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\TestResult;

class LabStats extends BaseWidget
{
    protected function getCards(): array
    {
        return [

            Card::make('Pending Tests', TestResult::where('test_status', 'pending')->count())
                ->description('Tests not completed yet')
                ->color('warning'),

            Card::make('Total Cost', TestResult::sum('total_cost') . ' EGP')
                ->description('Total cost of all tests')
                ->color('secondary'),
        ];
    }
}
