<?php

namespace Modules\Message\Policies;

use Modules\Message\Entities\Message;
use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MessagePolicy
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
     * Determine whether the user is auth user and can view any messages.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        $response = '';
        if($user->role==='brand')
        {
            $response = $this->isBrand($user);
        }
        if($user->role==='retailer')
        {
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
     * Determine whether the user is brand and can view his message.
     *
     * @param User $user
     * @param Message $message
     * @return bool
     */
    protected function isCreatorBrand(User $user, Message $message): bool
    {
        return $user->id === $message->brand_id;
    }

    /**
     * Check user can view his message details
     *
     * @param User $user
     * @param Message $message
     * @return bool|string
     */
    public function view(User $user, Message $message): bool|string
    {
        $response = '';
        if($user->role==='brand')
        {
            return $this->isCreatorBrand($user, $message);
        }
        if($user->role==='retailer')
        {
            return $this->isCreatorRetailer($user, $message);
        }
        return $response;
    }

    /**
     * Determine whether the user is retailer and can view his message.
     *
     * @param User $user
     * @param Message $message
     * @return bool
     */
    protected function isCreatorRetailer(User $user, Message $message): bool
    {

        return $user->id === $message->retailer_id;
    }

    /**
     * Determine whether the user can add message.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        $response = '';
        if($user->role==='brand')
        {
            $response = $this->isBrand($user);
        }
        if($user->role==='retailer')
        {
            $response = $this->isRetailer($user);
        }
        return $response;
    }
}
