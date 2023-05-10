<?php
 
namespace Kraenkvisuell\StatamicHelpers\Listeners;

use Statamic\Events\AssetUploaded;
use Kraenkvisuell\StatamicHelpers\Jobs\CreateAssetPresets;

class HandleAssetUploads
{
    public function handle(AssetUploaded $event): void
    {
        CreateAssetPresets::dispatch($event->asset);
    }
}
