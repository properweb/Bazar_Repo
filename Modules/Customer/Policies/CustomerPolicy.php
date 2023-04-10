<?php

namespace Modules\Customer\Policies;

use Modules\Customer\Entities\Customer;
use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
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
     * Determine whether the user can view the campaign.
     *
     * @param User $user
     * @param Customer $customer
     * @return bool
     */
    public function view(User $user, Customer $customer): bool
    {
        return $this->isCreator($user, $customer);
    }

    /**
     * Determine whether the user created the campaign.
     *
     * @param User $user
     * @param Customer $customer
     * @return bool
     */
    protected function isCreator(User $user, Customer $customer): bool
    {
        return $user->id === $customer->user_id;
    }

    /**
     * Determine whether the user can create campaigns.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->isBrand($user);
    }

    /**
     * Determine whether the user can update the campaign.
     *
     * @param User $user
     * @param Customer $customer
     * @return bool
     */
    public function update(User $user, Customer $customer): bool
    {
        return $this->isCreator($user, $customer);
    }

    /**
     * Determine whether the user can delete the campaign.
     *
     * @param User $user
     * @param Customer $customer
     * @return bool
     */
    public function delete(User $user, Customer $customer): bool
    {
        return $this->isCreator($user, $customer);
    }
}
