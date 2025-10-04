<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\TestResult;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LabStats extends BaseWidget
{

    public static function canView(): bool
    {
        return Auth::check() && Gate::allows('viewLabStats', \App\Dashboard::class);
    }


    protected function getCards(): array
    {
        return [

            Card::make('completed Tests(lab test)', TestResult::where('test_status', 'completed')->count())
                ->description('Tests  completed yet')
                  ->color('success'),

                 Card::make('Pending Tests (lab test)', TestResult::where('test_status', 'pending')->count())
                ->description('Tests not completed yet')
                ->color('warning'),

            Card::make('Total Cost (lab test)', TestResult::sum('total_cost') . ' EGP')
                ->description('Total cost of all tests')
                ->color('secondary'),
        ];
    }
}
