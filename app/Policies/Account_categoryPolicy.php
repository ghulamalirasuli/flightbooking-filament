<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Account_category;
use Illuminate\Auth\Access\HandlesAuthorization;

class Account_categoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AccountCategory');
    }

    public function view(AuthUser $authUser, Account_category $accountCategory): bool
    {
        return $authUser->can('View:AccountCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AccountCategory');
    }

    public function update(AuthUser $authUser, Account_category $accountCategory): bool
    {
        return $authUser->can('Update:AccountCategory');
    }

    public function delete(AuthUser $authUser, Account_category $accountCategory): bool
    {
        return $authUser->can('Delete:AccountCategory');
    }

    public function restore(AuthUser $authUser, Account_category $accountCategory): bool
    {
        return $authUser->can('Restore:AccountCategory');
    }

    public function forceDelete(AuthUser $authUser, Account_category $accountCategory): bool
    {
        return $authUser->can('ForceDelete:AccountCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AccountCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AccountCategory');
    }

    public function replicate(AuthUser $authUser, Account_category $accountCategory): bool
    {
        return $authUser->can('Replicate:AccountCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AccountCategory');
    }

}