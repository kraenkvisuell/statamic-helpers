<?php

namespace Kraenkvisuell\StatamicHelpers\Console;

use Statamic\Facades\Asset;
use Illuminate\Console\Command;
use Kraenkvisuell\StatamicHelpers\Jobs\CreateAssetPresets;

class RecreateAllPresets extends Command
{
    public $signature = 'kv:recreate-all-presets';

    public function handle()
    {
        $assets = Asset::whereContainer('assets')->all();

        foreach ($assets as $asset) {
            CreateAssetPresets::dispatch($asset);
        }

        $this->info('All recreating jobs have been put on the queue.');
    }
}
