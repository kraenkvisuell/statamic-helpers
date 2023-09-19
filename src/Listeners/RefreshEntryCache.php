<?php

namespace Kraenkvisuell\StatamicHelpers\Listeners;

use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\StatamicHelpers\Facades\Helper;

class RefreshEntryCache
{
    public function handle(object $event): void
    {
        $slug = $event->entry->slug;
        $key = $event?->entry?->collection?->handle.'.'.$slug.'.'.app()->getLocale();
        Cache::forget($key);

        Cache::rememberForever($key, function () use ($slug) {
            return Helper::entry(slug: $slug);
        });
    }
}
