<?php

namespace Modules\Product\Policies;

use Modules\Product\Entities\Product;
use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
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
     * Determine whether the user can view any campaigns.
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
     * Determine whether the user can view the product.
     *
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function view(User $user, Product $product): bool
    {
        return $this->isCreator($user, $product);
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
     * Determine whether the user can update the product.
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
     * Determine whether the user can delete the product.
     *
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function delete(User $user, Product $product): bool
    {
        return $this->isCreator($user, $product);
    }
}
