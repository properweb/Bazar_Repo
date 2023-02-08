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
     * Determine whether the user can view the post.
     *
     * @param \Modules\User\Entities\User  $user
     * @param \Modules\Campaign\Entities\Campaign  $campaign
     * @return mixed
     */
    public function view(User $user, Campaign $campaign)
    {
        return TRUE;
    }

    /**
     * Determine whether the user can create posts.
     *
     * @param \Modules\User\Entities\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->role == 'brand';
    }

    /**
     * Determine whether the user can update the post.
     *
     * @param \Modules\User\Entities\User  $user
     * @param \Modules\Campaign\Entities\Campaign  $campaign
     * @return mixed
     */
    public function update(User $user, Campaign $campaign)
    {
        return $user->id === $campaign->user_id;
    }

    /**
     * Determine whether the user can delete the campaign.
     *
     * @param \Modules\User\Entities\User  $user
     * @param \Modules\Campaign\Entities\Campaign  $campaign
     * @return mixed
     */
    public function delete(User $user, Campaign $campaign)
    {
        return $user->id === $campaign->user_id;
    }


}
