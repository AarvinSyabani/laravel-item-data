<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view transactions
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return true; // All authenticated users can view individual transactions
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only users with 'create_transaction' permission can create transactions
        return $user->can('create_transaction');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        // Only users with 'update_transaction' permission can update transactions
        return $user->can('update_transaction');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        // Only users with 'delete_transaction' permission can delete transactions
        return $user->can('delete_transaction');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Transaction $transaction): bool
    {
        // Only users with 'restore_transaction' permission can restore transactions
        return $user->can('restore_transaction');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Transaction $transaction): bool
    {
        // Only users with 'force_delete_transaction' permission can force delete transactions
        return $user->can('force_delete_transaction');
    }
    
    /**
     * Determine whether the user can process incoming transactions.
     */
    public function processIncoming(User $user): bool
    {
        // Only users with 'process_incoming_transaction' permission can process incoming transactions
        return $user->can('process_incoming_transaction');
    }
    
    /**
     * Determine whether the user can process outgoing transactions.
     */
    public function processOutgoing(User $user): bool
    {
        // Only users with 'process_outgoing_transaction' permission can process outgoing transactions
        return $user->can('process_outgoing_transaction');
    }
}
