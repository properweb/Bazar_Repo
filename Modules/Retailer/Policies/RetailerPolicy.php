<?php

namespace Modules\Retailer\Policies;

use Modules\Retailer\Entities\Retailer;
use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RetailerPolicy
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
     * Determine whether the user can view the retailer.
     *
     * @param User $user
     * @param Retailer $retailer
     * @return bool
     */
    public function view(User $user, Retailer $retailer): bool
    {
        return $this->isOwner($user, $retailer);
    }

    /**
     * Determine whether the user can update the retailer.
     *
     * @param User $user
     * @param Retailer $retailer
     * @return bool
     */
    public function update(User $user, Retailer $retailer): bool
    {
        return $this->isOwner($user, $retailer);
    }

    /**
     * Determine whether the user is retailer.
     *
     * @param User $user
     * @return bool
     */
    protected function isRetailer(User $user): bool
    {
        return $user->role === User::ROLE_RETAILER;
    }

    /**
     * Determine whether the user created the retailer.
     *
     * @param User $user
     * @param Retailer $retailer
     * @return bool
     */
    protected function isOwner(User $user, Retailer $retailer): bool
    {
        return $user->id === $retailer->user_id;
    }
}
