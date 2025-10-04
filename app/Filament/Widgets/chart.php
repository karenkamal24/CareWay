<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;



class Chart extends ChartWidget
{
    public static function canView(): bool
    {
        return Auth::check() && Gate::allows('viewOrdersStatsOverview', \App\Dashboard::class);
    }
    protected static ?string $heading = 'Orders Per Year';

    protected static ?int $height = 250;

    public ?string $filter = null;

    protected function getFilters(): array
    {
        $currentYear = now()->year;
        $years = range($currentYear, $currentYear - 5);

        return collect($years)->mapWithKeys(fn ($year) => [(string) $year => (string) $year])->toArray();
    }

    protected function getData(): array
    {
        $selectedYear = $this->filter ?? now()->year;

        if (!is_numeric($selectedYear)) {
            $selectedYear = now()->year;
        }

        $months = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug',
            '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec'
        ];

        $orders = Trend::model(Order::class)
            ->between(
                start: Carbon::createFromDate($selectedYear, 1, 1),
                end: Carbon::createFromDate($selectedYear, 12, 31),
            )
            ->perMonth()
            ->count()
            ->mapWithKeys(fn (TrendValue $value) => [
                Carbon::parse($value->date)->format('m') => $value->aggregate
            ]);

        $orderCounts = [];
        foreach ($months as $key => $month) {
            $orderCounts[] = $orders[$key] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => "Orders in $selectedYear",
                    'data' => $orderCounts,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => array_values($months),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

}
