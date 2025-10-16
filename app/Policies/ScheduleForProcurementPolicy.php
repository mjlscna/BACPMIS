<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ScheduleForProcurement;
use Illuminate\Auth\Access\HandlesAuthorization;

class ScheduleForProcurementPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_schedule::for::procurement');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ScheduleForProcurement $scheduleForProcurement): bool
    {
        return $user->can('view_schedule::for::procurement');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_schedule::for::procurement');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ScheduleForProcurement $scheduleForProcurement): bool
    {
        return $user->can('update_schedule::for::procurement');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ScheduleForProcurement $scheduleForProcurement): bool
    {
        return $user->can('delete_schedule::for::procurement');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_schedule::for::procurement');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, ScheduleForProcurement $scheduleForProcurement): bool
    {
        return $user->can('force_delete_schedule::for::procurement');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_schedule::for::procurement');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, ScheduleForProcurement $scheduleForProcurement): bool
    {
        return $user->can('restore_schedule::for::procurement');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_schedule::for::procurement');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, ScheduleForProcurement $scheduleForProcurement): bool
    {
        return $user->can('replicate_schedule::for::procurement');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_schedule::for::procurement');
    }
}
