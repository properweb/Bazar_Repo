<?php

namespace Modules\Analytic\Policies;

use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AnalyticPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user is auth user and can view any orders.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->isBrand($user);
    }

    /**
     * Determine whether the user is brand.
     *
     * @param User $user
     * @return bool
     */
    protected function isBrand(User $user): bool
    {
        return $user->role === User::ROLE_BRAND;
    }
}
