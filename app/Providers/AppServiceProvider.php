<?php

namespace App\Providers;

use App\Channel;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        \View::composer('*', function($view) {
            $view->with('channels', \App\Channel::all());
        });
    }
}
