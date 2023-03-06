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
