<?php

namespace Modules\Brand\Policies;

use Modules\Brand\Entities\Brand;
use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BrandPolicy
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
     * Determine whether the user can update the brand.
     *
     * @param User $user
     * @param Brand $brand
     * @return bool
     */
    public function update(User $user, Brand $brand): bool
    {
        return $this->isOwner($user, $brand);
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
     * Determine whether the user created the brand.
     *
     * @param User $user
     * @param Brand $brand
     * @return bool
     */
    protected function isOwner(User $user, Brand $brand): bool
    {
        return $user->id === $brand->user_id;
    }
}
