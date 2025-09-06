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
        return $user->hasPermissionTo('widget_OrdersStatsOverview');
    }

    public function view(User $user)
    {
        return $user->hasPermissionTo('view_chart');
    }

    public function viewOrderstuts(User $user)
    {
        return $user->hasPermissionTo('widget_orderstuts');
    }

    public function viewLabStats(User $user)
    {
        return $user->hasPermissionTo('widget_LabStats');
    }
}
