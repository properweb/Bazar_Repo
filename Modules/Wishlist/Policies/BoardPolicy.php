<?php

namespace Modules\Wishlist\Policies;

use Modules\Wishlist\Entities\Board;

use Modules\User\Entities\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class BoardPolicy
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
     * Determine whether the user can view any board.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->isRetailer($user);
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
     * Determine whether the user created the board.
     *
     * @param User $user
     * @param Board $board
     * @return bool
     */
    protected function isCreator(User $user, Board $board): bool
    {
        return $user->id === $board->user_id;
    }


    /**
     * Determine whether the user can view board detail.
     *
     * @param User $user
     * @param Board $board
     * @return bool
     */

    public function view(User $user, Board $board): bool
    {
        return $this->isCreator($user, $board);
    }

    /**
     * Determine whether the user can update their board.
     *
     * @param User $user
     * @param Board $board
     * @return bool
     */
    public function update(User $user, Board $board): bool
    {
        return $this->isCreator($user, $board);
    }

    /**
     * Determine whether the user can delete their board.
     *
     * @param User $user
     * @param Board $board
     * @return bool
     */
    public function delete(User $user, Board $board): bool
    {
        return $this->isCreator($user, $board);
    }

    /**
     * Determine whether the user can add their board.
     *
     * @param User $user
     * @return bool
     */

    public function create(User $user): bool
    {
        return $this->isRetailer($user);
    }


}
