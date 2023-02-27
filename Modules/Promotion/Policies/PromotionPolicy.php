<?php

namespace Modules\Promotion\Policies;

use Modules\Promotion\Entities\Promotion;
use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PromotionPolicy
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
     * Determine whether the user can view any Promotions.
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
     * Determine whether the user can view the Promotion.
     *
     * @param User $user
     * @param Promotion $promotion
     * @return bool
     */
    public function view(User $user, Promotion $promotion): bool
    {
        return $this->isCreator($user, $promotion);
    }

    /**
     * Determine whether the user created the Promotion.
     *
     * @param User $user
     * @param Promotion $promotion
     * @return bool
     */
    protected function isCreator(User $user, Promotion $promotion): bool
    {
        return $user->id === $promotion->user_id;
    }

    /**
     * Determine whether the user can create Promotions.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isBrand($user);
    }

    /**
     * Determine whether the user can update the Promotion.
     *
     * @param User $user
     * @param Promotion $promotion
     * @return bool
     */
    public function update(User $user, Promotion $promotion): bool
    {
        return $this->isCreator($user, $promotion);
    }

    /**
     * Determine whether the user can delete the Promotion.
     *
     * @param User $user
     * @param Promotion $promotion
     * @return bool
     */
    public function delete(User $user, Promotion $promotion): bool
    {
        return $this->isCreator($user, $promotion);
    }


}
