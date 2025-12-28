<?php

namespace App\Policies;

use App\Models\Account_ledger;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class Account_ledgerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Account_ledger $accountLedger): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Account_ledger $accountLedger): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Account_ledger $accountLedger): bool
    {
        return true;
    }

  
    
    public function restore(User $user, Account_ledger $accountLedger): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Account_ledger $accountLedger): bool
    {
        return true;
    }
}
