<?php

namespace App\Providers;

use App\User;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Silvanite\NovaToolPermissions\NovaToolPermissions;
use Usersettings\Maincard\Maincard;
use Usersettings\Userinformation\Userinformation;
use Usersettings\Userprofile\Userprofile;
use Usersettings\Userslist\Userslist;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
                ->withAuthenticationRoutes()
                ->withPasswordResetRoutes()
                ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        //
    }

    /**
     * Get the cards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function cards()
    {
        return [
            new Maincard()
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        /**
         * @var User $user
         */
        $user = auth()->user();

        return [
            (new NovaToolPermissions())->canSee(function () use ($user) {
                return $user->isModerator();
            }),
            (new Userslist())->canSee(function () use ($user){
                return $user->isModerator();
            }),
            (new Userinformation())->canSee(function () use ($user){
                return $user->isModerator() || ($user->isDoctor() && $user->isModeratorConfirmed());
            }),
            (new Userprofile())->canSee(function () use ($user){
                return $user->isRegistered();
            })
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
