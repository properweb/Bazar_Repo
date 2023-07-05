<?php

namespace Modules\Shopify\Policies;

use Modules\Product\Entities\Product;
use Modules\Shopify\Entities\Webhook;
use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShopifyPolicy
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
     * Determine whether the user can view .
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->isBrand($user);
    }

    /**
     * Determine whether the user is Brand.
     *
     * @param User $user
     * @return bool
     */
    protected function isBrand(User $user): bool
    {
        return $user->role === User::ROLE_BRAND;
    }

    /**
     * Determine whether the user can create products.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isBrand($user);
    }

    /**
     * Determine whether the user created the product.
     *
     * @param User $user
     * @param Product $product
     * @return bool
     */
    protected function isCreator(User $user, Product $product): bool
    {
        return $user->id === $product->user_id;
    }

    /**
     * Determine whether the user can update products.
     *
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function update(User $user, Product $product): bool
    {
        return $this->isCreator($user, $product);
    }

    /**
     * Determine whether the user can delete webhook.
     *
     * @param User $user
     * @param Webhook $webhook
     * @return bool
     */
    protected function isWebCreator(User $user, Webhook $webhook): bool
    {
        return $user->id === $webhook->user_id;
    }

    /**
     * Determine whether the user can delete webhook.
     *
     * @param User $user
     * @param Webhook $webhook
     * @return bool
     */
    public function delete(User $user, Webhook $webhook): bool
    {
        return $this->isWebCreator($user, $webhook);
    }
}
