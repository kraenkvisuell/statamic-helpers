<?php

namespace Kraenkvisuell\StatamicHelpers\Listeners;

use Illuminate\Support\Facades\Cache;

class RemoveFromEntryCache
{
    public function handle(object $event): void
    {
        if ($event?->entry?->collection?->handle === 'pages') {
            $slug = $event->entry->slug;
            Cache::forget('page.'.$slug);
        }
    }
}
