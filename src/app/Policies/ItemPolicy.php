<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ItemPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view items
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Item $item): bool
    {
        return true; // All authenticated users can view individual items
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only users with 'create_item' permission can create items
        return $user->can('create_item');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Item $item): bool
    {
        // Only users with 'update_item' permission can update items
        return $user->can('update_item');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Item $item): bool
    {
        // Only users with 'delete_item' permission can delete items
        return $user->can('delete_item');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Item $item): bool
    {
        // Only users with 'restore_item' permission can restore items
        return $user->can('restore_item');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Item $item): bool
    {
        // Only users with 'force_delete_item' permission can force delete items
        return $user->can('force_delete_item');
    }
    
    /**
     * Determine whether the user can update the stock of an item.
     */
    public function updateStock(User $user, Item $item): bool
    {
        // Only users with 'update_item_stock' permission can update item stock
        return $user->can('update_item_stock');
    }
}
