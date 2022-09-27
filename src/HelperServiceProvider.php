<?php

namespace Kraenkvisuell\StatamicHelpers;

use Illuminate\Support\ServiceProvider;
use Kraenkvisuell\StatamicHelpers\Console\DownloadAssets;
use Kraenkvisuell\StatamicHelpers\Console\DownloadCollections;
use Kraenkvisuell\StatamicHelpers\Console\UploadAssets;
use Kraenkvisuell\StatamicHelpers\Console\UploadCollections;
use Kraenkvisuell\StatamicHelpers\Console\UploadGlobals;

class HelperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/statamic-helpers.php' => config_path('statamic-helpers.php'),
        ], 'statamic-helpers');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DownloadAssets::class,
                DownloadCollections::class,
                UploadAssets::class,
                UploadCollections::class,
                UploadGlobals::class,
            ]);
        }
    }

    public function register()
    {
        //
    }
}
