<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DeliveryZone;
use Illuminate\Auth\Access\HandlesAuthorization;

class DeliveryZonePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('delivery_zone_view_any');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DeliveryZone $deliveryZone): bool
    {
        return $user->can('delivery_zone_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('delivery_zone_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DeliveryZone $deliveryZone): bool
    {
        return $user->can('delivery_zone_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DeliveryZone $deliveryZone): bool
    {
        return $user->can('delivery_zone_delete');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delivery_zone_delete_any');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, DeliveryZone $deliveryZone): bool
    {
        return $user->can('delivery_zone_restore');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('delivery_zone_force_delete');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, DeliveryZone $deliveryZone): bool
    {
        return $user->can('delivery_zone_restore');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('delivery_zone_restore_any');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, DeliveryZone $deliveryZone): bool
    {
        return $user->can('delivery_zone_replicate');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('delivery_zone_reorder');
    }
}
