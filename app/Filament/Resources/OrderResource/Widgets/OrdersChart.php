<?php

namespace App\Filament\Resources\OrderResource\Widgets;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';
    protected static string $color = 'info';

    protected function getData(): array
    {
    
        $orders = Order::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
        ];

        
        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => array_values($orders),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
                    'borderColor' => 'rgba(54, 162, 235, 1)', 
                    'borderWidth' => 1,
                ],
            ],
            'labels' => array_map(fn($month) => $months[$month], array_keys($orders)),
        ];
    }


    protected function getType(): string
    {
        return 'bar';
    }
}
