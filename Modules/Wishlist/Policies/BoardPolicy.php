<?php

namespace Modules\Wishlist\Policies;

use Modules\Board\Entities\Board;

use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BoardPolicy
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
     * Determine whether the user can view any wishlist.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {

        return true;
        // return $this->isRetailer($user);
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


}
