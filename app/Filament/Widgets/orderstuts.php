<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class orderstuts extends ChartWidget
{   public static function canView(): bool
    {
        return Auth::check() && Gate::allows('viewOrdersStatsOverview', \App\Dashboard::class);
    }
    protected static ?string $heading = 'Orders by Status';
    protected static ?int $height = 150;

    protected function getData(): array
    {
        $orders = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => array_values($orders),
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                    ],
                ],
            ],
            'labels' => array_keys($orders),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'responsive' => true,
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
        ];
    }

    protected function getContainerAttributes(): array
    {
        return [
            'style' => 'max-height: 200px;', 
        ];
    }
}
