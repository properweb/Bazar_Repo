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
     * Determine whether the user is auth user and can view any orders.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        $response = '';
        if ($user->role === 'brand') {
            $response = $this->isBrand($user);
        }
        if ($user->role === 'retailer') {
            $response = $this->isRetailer($user);
        }
        return $response;
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
     * Determine whether the user is retailer and can check out.
     *
     * @param User $user
     * @return bool
     */
    public function checkout(User $user): bool
    {
        return $this->isRetailer($user);
    }

    /**
     * Determine whether the user is retailer and can update billing address.
     *
     * @param User $user
     * @return bool
     */
    public function update(User $user): bool
    {
        return $this->isRetailer($user);
    }

    /**
     * Check user can view his order details
     *
     * @param User $user
     * @param Order $order
     * @return bool|string
     */
    public function view(User $user, Order $order): bool|string
    {
        $response = '';
        if ($user->role === 'brand') {
            return $this->isCreatorBrand($user, $order);
        }
        if ($user->role === 'retailer') {
            return $this->isCreatorRetailer($user, $order);
        }
        return $response;
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

    /**
     * Update Order
     *
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function updateOrder(User $user, Order $order): bool
    {
        return $this->isCreatorBrand($user, $order);
    }

    /**
     * Cancel Order
     *
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function cancel(User $user, Order $order): bool
    {
        return $this->isCreatorBrand($user, $order);
    }

    /**
     * Brand accept his order
     *
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function accept(User $user, Order $order): bool
    {
        return $this->isCreatorBrand($user, $order);
    }

    /**
     * Change shipping address
     *
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function changeAdders(User $user, Order $order): bool
    {
        return $this->isCreatorBrand($user, $order);
    }

    /**
     * Determine whether the user is retailer and can review order.
     *
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function review(User $user, Order $order): bool
    {
        return $this->isRetailer($user);
    }

    /**
     * Cancel order request
     *
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function cancelRequest(User $user, Order $order): bool
    {
        if ($this->isRetailer($user) === true) {
            return $user->user_id === $order->user_id;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user is retailer.
     *
     * @param User $user
     * @param OrderReview $orderReview
     * @return bool
     */
    protected function isReviewer(User $user, OrderReview $orderReview): bool
    {

        return $user->user_id === $orderReview->user_id;
    }
}
