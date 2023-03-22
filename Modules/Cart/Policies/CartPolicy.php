<?php

namespace Modules\Cart\Policies;


use Modules\Cart\Entities\Cart;
use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CartPolicy
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
     * Determine whether the user can view the Cart.
     *
     * @param User $user
     * @return bool
     */
    public function view(User $user): bool
    {
        return $this->isRetailer($user);
    }

    /**
     * Determine whether the user created the cart.
     *
     * @param User $user
     * @param Cart $cart
     * @return bool
     */
    protected function isCreator(User $user, Cart $cart): bool
    {
        return $user->id === $cart->user_id;
    }

    /**
     * Determine whether the user can create carts.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isRetailer($user);
    }

    /**
     * Check the user is retailer
     *
     * @param User $user
     * @return bool
     */

    protected function isRetailer(User $user): bool
    {
        return $user->role === User::ROLE_RETAILER;
    }

    /**
     * Determine whether the user can update the cart.
     *
     * @param User $user
     * @param Cart $cart
     * @return bool
     */
    public function delete(User $user,Cart $cart): bool
    {

        return $this->isCreator($user,$cart);
    }

    /**
     * Determine whether the user can update the cart.
     *
     * @param User $user
     * @param Cart $cart
     * @return bool
     */
    public function update(User $user,Cart $cart): bool
    {

        return $this->isCreator($user,$cart);
    }
}
