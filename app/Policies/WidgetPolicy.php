<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class WidgetPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the orders stats overview.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewOrdersStatsOverview(User $user)
    {
        return $user->can('widget_OrdersStatsOverview');
    }

    /**
     * Determine whether the user can view the chart.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewChart(User $user)
    {
        return $user->can('widget_chart');
    }

    /**
     * Determine whether the user can view the order stats.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewOrderstuts(User $user)
    {
        return $user->can('widget_orderstuts');
    }
}
