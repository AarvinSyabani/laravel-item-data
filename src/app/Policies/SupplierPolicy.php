<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplierPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view suppliers
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Supplier $supplier): bool
    {
        return true; // All authenticated users can view individual suppliers
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only users with 'create_supplier' permission can create suppliers
        return $user->can('create_supplier');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Supplier $supplier): bool
    {
        // Only users with 'update_supplier' permission can update suppliers
        return $user->can('update_supplier');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Supplier $supplier): bool
    {
        // Only users with 'delete_supplier' permission can delete suppliers
        return $user->can('delete_supplier');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Supplier $supplier): bool
    {
        // Only users with 'restore_supplier' permission can restore suppliers
        return $user->can('restore_supplier');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Supplier $supplier): bool
    {
        // Only users with 'force_delete_supplier' permission can force delete suppliers
        return $user->can('force_delete_supplier');
    }
}
