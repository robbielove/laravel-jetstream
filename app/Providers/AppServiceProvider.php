<?php

namespace App\Providers;

use App\Jobs\DiscordBotJob;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        DiscordBotJob::dispatch();
        // if the job fails retry it

    }
}
