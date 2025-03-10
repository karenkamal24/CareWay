<?php

namespace App\Filament\Widgets;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
class OrdersStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return Auth::check() && Gate::allows('viewOrdersStatsOverview', \App\Dashboard::class);
    }
    protected function getCards(): array
    {
        return [
           
            Card::make('Total Orders', Order::count())
                ->description('All orders placed')
                ->color('primary')
                ->icon('heroicon-o-shopping-cart'),

          
            Card::make('Total Revenue', number_format(Order::where('status', 'delivered')->sum('total_price'), 2) . ' $')
                ->description('Earnings from completed orders')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            // عدد المستخدمين الفريدين الذين طلبوا
            Card::make('Total Customers', Order::distinct('user_id')->count())
                ->description('Unique customers who placed orders')
                ->color('info')
                ->icon('heroicon-o-user-group'),
        ];
    }

}
