<?php

namespace Modules\Shipping\Policies;

use Modules\Shipping\Entities\Shipping;
use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShippingPolicy
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
     * Determine whether the user can view any Shipping.
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

    /**
     * Determine whether the user can view the campaign.
     *
     * @param User $user
     * @param Shipping $shipping
     * @return bool
     */
    public function view(User $user, Shipping $shipping): bool
    {
        return $this->isCreator($user, $shipping);
    }

    /**
     * Determine whether the user created the Shipping.
     *
     * @param User $user
     * @param Shipping $shipping
     * @return bool
     */
    protected function isCreator(User $user, Shipping $shipping): bool
    {
        return $user->id === $shipping->user_id;
    }

    /**
     * Determine whether the user can create Shippings.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isBrand($user);
    }

    /**
     * Determine whether the user can update the Shipping.
     *
     * @param User $user
     * @param Shipping $shipping
     * @return bool
     */
    public function update(User $user, Shipping $shipping): bool
    {
        return $this->isCreator($user, $shipping);
    }

    /**
     * Determine whether the user can delete the Shipping.
     *
     * @param User $user
     * @param Shipping $shipping
     * @return bool
     */
    public function delete(User $user, Shipping $shipping): bool
    {
        return $this->isCreator($user, $shipping);
    }
}
