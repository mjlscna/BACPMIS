<?php

namespace App\Policies;

use App\Models\User;
use App\Models\BiddingStatus;
use Illuminate\Auth\Access\HandlesAuthorization;

class BiddingStatusPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_bidding::status');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BiddingStatus $biddingStatus): bool
    {
        return $user->can('view_bidding::status');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_bidding::status');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BiddingStatus $biddingStatus): bool
    {
        return $user->can('update_bidding::status');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BiddingStatus $biddingStatus): bool
    {
        return $user->can('delete_bidding::status');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_bidding::status');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, BiddingStatus $biddingStatus): bool
    {
        return $user->can('force_delete_bidding::status');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_bidding::status');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, BiddingStatus $biddingStatus): bool
    {
        return $user->can('restore_bidding::status');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_bidding::status');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, BiddingStatus $biddingStatus): bool
    {
        return $user->can('replicate_bidding::status');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_bidding::status');
    }
}
