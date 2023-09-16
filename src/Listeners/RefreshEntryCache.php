<?php

namespace Kraenkvisuell\StatamicHelpers\Listeners;

use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\StatamicHelpers\Facades\Helper;

class RefreshEntryCache
{
    public function handle(object $event): void
    {
        if ($event?->entry?->collection?->handle === 'pages') {
            $slug = $event->entry->slug;
            Cache::forget('page.'.$slug);

            Cache::rememberForever('page.'.$slug, function () use ($slug) {
                return Helper::entry(slug: $slug);
            });
        }
    }
}
