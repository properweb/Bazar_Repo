<?php

namespace Modules\Order\Policies;

use Modules\Order\Entities\Order;
use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
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
     * Determine whether the user is brand and can view any orders.
     *
     * @param User $user
     * @return bool
     */
    public function viewAnyBrand(User $user): bool
    {
        return $this->isBrand($user);
    }

    /**
     * Determine whether the user is retailer and can view any orders.
     *
     * @param User $user
     * @return bool
     */
    public function viewAnyRetailer(User $user): bool
    {
        return $this->isRetailer($user);
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
     * Determine whether the user is brand and can view his order.
     *
     * @param User $user
     * @param Order $order
     * @return bool
     */
    protected function isCreatorBrand(User $user, Order $order): bool
    {
        return $user->id === $order->brand_id;
    }

    /**
     * Determine whether the user is brand and can view his order.
     *
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function viewBrand(User $user, Order $order): bool
    {
        return $this->isCreatorBrand($user, $order);
    }

    /**
     * Determine whether the user is retailer and can view his order.
     *
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function viewRetailer(User $user, Order $order): bool
    {

        return $this->isCreatorRetailer($user, $order);
    }

    /**
     * Determine whether the user is retailer and can view his order.
     *
     * @param User $user
     * @param Order $order
     * @return bool
     */
    protected function isCreatorRetailer(User $user, Order $order): bool
    {

        return $user->id === $order->user_id;
    }
}
