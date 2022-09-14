<?php

namespace Kraenkvisuell\StatamicHelpers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        require_once __DIR__.'/../helpers/helpers.php';
    }

    public function register()
    {
        //
    }
}
