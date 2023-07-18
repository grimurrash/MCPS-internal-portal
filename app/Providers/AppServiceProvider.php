<?php

namespace App\Providers;

use App\Exceptions\Console\Commands\SendMailsCommand;
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
        date_default_timezone_set('Europe/Moscow');
        $this->commands([
            SendMailsCommand::class
        ]);
    }
}
