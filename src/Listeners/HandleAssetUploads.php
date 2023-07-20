<?php

namespace Kraenkvisuell\StatamicHelpers\Listeners;

use Kraenkvisuell\StatamicHelpers\Jobs\CreateAssetPresets;
use Statamic\Events\AssetUploaded;

class HandleAssetUploads
{
    public function handle(AssetUploaded $event): void
    {
        CreateAssetPresets::dispatch($event->asset);
    }
}
