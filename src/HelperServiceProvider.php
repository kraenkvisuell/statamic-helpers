<?php

namespace Kraenkvisuell\StatamicHelpers;

use Illuminate\Support\ServiceProvider;
use Kraenkvisuell\StatamicHelpers\Console\DownloadAssets;
use Kraenkvisuell\StatamicHelpers\Console\DownloadCollections;
use Kraenkvisuell\StatamicHelpers\Console\UploadAssets;

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
            ]);
        }
    }

    public function register()
    {
        //
    }
}
