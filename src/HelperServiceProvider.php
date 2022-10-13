<?php

namespace Kraenkvisuell\StatamicHelpers;

use Illuminate\Support\ServiceProvider;
use Kraenkvisuell\StatamicHelpers\Console\DownloadAll;
use Kraenkvisuell\StatamicHelpers\Console\DownloadAssets;
use Kraenkvisuell\StatamicHelpers\Console\DownloadCollections;
use Kraenkvisuell\StatamicHelpers\Console\DownloadGlobals;
use Kraenkvisuell\StatamicHelpers\Console\DownloadTrees;
use Kraenkvisuell\StatamicHelpers\Console\UploadAll;
use Kraenkvisuell\StatamicHelpers\Console\UploadAssets;
use Kraenkvisuell\StatamicHelpers\Console\UploadCollections;
use Kraenkvisuell\StatamicHelpers\Console\UploadGlobals;
use Kraenkvisuell\StatamicHelpers\Console\UploadTrees;

class HelperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/statamic-helpers.php' => config_path('statamic-helpers.php'),
        ], 'statamic-helpers');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DownloadAll::class,
                DownloadAssets::class,
                DownloadCollections::class,
                DownloadGlobals::class,
                DownloadTrees::class,
                UploadAll::class,
                UploadAssets::class,
                UploadCollections::class,
                UploadGlobals::class,
                UploadTrees::class,
            ]);
        }
    }

    public function register()
    {
        $this->app->bind('helper', fn () => new Helper());
    }
}
