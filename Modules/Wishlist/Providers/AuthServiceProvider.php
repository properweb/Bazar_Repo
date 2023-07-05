<?php

namespace Modules\Wishlist\Providers;


use Modules\Wishlist\Entities\Wishlist;
use Modules\Wishlist\Entities\Board;
use Modules\Wishlist\Policies\WishlistPolicy;
use Modules\Wishlist\Policies\BoardPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Wishlist::class => WishlistPolicy::class,
        Board::class => BoardPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}



