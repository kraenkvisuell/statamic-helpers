<?php

namespace Kraenkvisuell\StatamicHelpers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Kraenkvisuell\StatamicHelpers\Console\DownloadAll;
use Kraenkvisuell\StatamicHelpers\Console\DownloadAssets;
use Kraenkvisuell\StatamicHelpers\Console\DownloadCollections;
use Kraenkvisuell\StatamicHelpers\Console\DownloadGlobals;
use Kraenkvisuell\StatamicHelpers\Console\DownloadTrees;
use Kraenkvisuell\StatamicHelpers\Console\GenerateDummySite;
use Kraenkvisuell\StatamicHelpers\Console\RecreateAllPresets;
use Kraenkvisuell\StatamicHelpers\Console\UploadAll;
use Kraenkvisuell\StatamicHelpers\Console\UploadAssets;
use Kraenkvisuell\StatamicHelpers\Console\UploadCollections;
use Kraenkvisuell\StatamicHelpers\Console\UploadGlobals;
use Kraenkvisuell\StatamicHelpers\Console\UploadTrees;
use Kraenkvisuell\StatamicHelpers\Listeners\HandleAssetUploads;
use Kraenkvisuell\StatamicHelpers\Listeners\RefreshEntryCache;
use Kraenkvisuell\StatamicHelpers\Listeners\RefreshGlobalCache;
use Kraenkvisuell\StatamicHelpers\Listeners\RefreshNavCache;
use Kraenkvisuell\StatamicHelpers\Listeners\RemoveFromEntryCache;
use Statamic\Events\AssetUploaded;
use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;
use Statamic\Events\GlobalSetSaved;
use Statamic\Events\NavSaved;
use Statamic\Events\NavTreeSaved;

class HelperServiceProvider extends ServiceProvider
{
    public function boot()
    {

//        Event::listen(
//            AssetUploaded::class,
//            [HandleAssetUploads::class, 'handle']
//        );

//        Event::listen(
//            EntrySaved::class,
//            [RefreshEntryCache::class, 'handle']
//        );

//        Event::listen(
//            EntryDeleted::class,
//            [RemoveFromEntryCache::class, 'handle']
//        );

        Event::listen(
            GlobalSetSaved::class,
            [RefreshGlobalCache::class, 'handle']
        );

        Event::listen(
            [NavSaved::class, NavTreeSaved::class],
            [RefreshNavCache::class, 'handle']
        );

        //        Event::listen(
        //            NavTreeSaved::class,
        //            [RefreshNavCache::class, 'handle']
        //        );

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
