<?php

namespace Kraenkvisuell\StatamicTransferContent;

use Kraenkvisuell\StatamicTransferContent\Console\AssetsToProduction;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        $this->publishes([
            __DIR__.'/../config/transfer-content.php' => config_path('transfer-content.php'),
        ], 'transfer-content');
        
        if ($this->app->runningInConsole()) {
            $this->commands([
                AssetsToProduction::class,
            ]);
        }
    }   
}
