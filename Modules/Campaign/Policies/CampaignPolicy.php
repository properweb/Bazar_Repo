<?php

namespace Modules\Campaign\Policies;

use Modules\Campaign\Entities\Campaign;
use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampaignPolicy
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
        return $user->role === 'brand';
    }

    /**
     * Determine whether the user can view the campaign.
     *
     * @param User $user
     * @param Campaign $campaign
     * @return bool
     */
    public function view(User $user, Campaign $campaign): bool
    {
        return $user->id === $campaign->user_id;
    }


    /**
     * Determine whether the user can create campaigns.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {

        return $user->role === 'brand';
    }

    /**
     * Determine whether the user can update the campaign.
     *
     * @param User $user
     * @param Campaign $campaign
     * @return bool
     */
    public function update(User $user, Campaign $campaign): bool
    {
        return $user->id === $campaign->user_id;
    }

    /**
     * Determine whether the user can delete the campaign.
     *
     * @param User $user
     * @param Campaign $campaign
     * @return bool
     */
    public function delete(User $user, Campaign $campaign): bool
    {
        return $user->id === $campaign->user_id;
    }


}
