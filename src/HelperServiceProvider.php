<?php

namespace Kraenkvisuell\StatamicHelpers;

use Statamic\Events\AssetUploaded;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;
use Kraenkvisuell\StatamicHelpers\Console\UploadAll;
use Kraenkvisuell\StatamicHelpers\Console\DownloadAll;
use Kraenkvisuell\StatamicHelpers\Console\UploadTrees;
use Kraenkvisuell\StatamicHelpers\Console\UploadAssets;
use Kraenkvisuell\StatamicHelpers\Console\DownloadTrees;
use Kraenkvisuell\StatamicHelpers\Console\UploadGlobals;
use Kraenkvisuell\StatamicHelpers\Console\DownloadAssets;
use Kraenkvisuell\StatamicHelpers\Console\DownloadGlobals;
use Kraenkvisuell\StatamicHelpers\Console\GenerateDummySite;
use Kraenkvisuell\StatamicHelpers\Console\UploadCollections;
use Kraenkvisuell\StatamicHelpers\Console\DownloadCollections;
use Kraenkvisuell\StatamicHelpers\Console\RecreateAllPresets;
use Kraenkvisuell\StatamicHelpers\Listeners\HandleAssetUploads;

class HelperServiceProvider extends ServiceProvider
{
    public function boot()
    {
        JsonResource::withoutWrapping();

        Event::listen(
            AssetUploaded::class,
            [HandleAssetUploads::class, 'handle']
        );
        
        $this->publishes([
            __DIR__.'/../config/statamic-helpers.php' => config_path('statamic-helpers.php'),
        ], 'statamic-helpers');

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateDummySite::class,
                RecreateAllPresets::class,
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

        require_once __DIR__.'/../helpers/helpers.php';
    }

    public function register()
    {
        $this->app->bind('helper', fn () => new Helper());
    }
}
