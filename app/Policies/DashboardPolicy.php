<?php

namespace App\Policies;

use App\Models\User;

class DashboardPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewOrdersStatsOverview(User $user): bool
    {
        return $user->hasPermissionTo('widget_OrdersStatsOverview');
    }

    public function view(User $user): bool
    {
        return $user->hasPermissionTo('view_chart');
    }

    public function viewOrderstuts(User $user): bool
    {
        return $user->hasPermissionTo('widget_orderstuts');
    }
}
