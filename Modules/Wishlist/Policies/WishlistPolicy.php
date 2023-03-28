<?php

namespace Modules\Wishlist\Policies;

use Modules\Wishlist\Entities\Wishlist;
use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class WishlistPolicy
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
        return $this->isRetailer($user);
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
     * Determine whether the user created the wishlist.
     *
     * @param User $user
     * @param Wishlist $wishList
     * @return bool
     */
    protected function isCreator(User $user, Wishlist $wishList): bool
    {
        return $user->id === $wishList->user_id;
    }

    /**
     * Determine whether the user can create wishlists.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isRetailer($user);
    }

    /**
     * Determine whether the user can update the wishlist.
     *
     * @param User $user
     * @param Wishlist $wishList
     * @return bool
     */
    public function update(User $user, Wishlist $wishList): bool
    {
        return $this->isCreator($user, $wishList);
    }

    /**
     * Determine whether the user can delete the wishlist.
     *
     * @param User $user
     * @param Wishlist $wishList
     * @return bool
     */
    public function delete(User $user, Wishlist $wishList): bool
    {
        return $this->isCreator($user, $wishList);
    }
}
