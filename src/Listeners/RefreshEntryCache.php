<?php

namespace Kraenkvisuell\StatamicHelpers\Listeners;

use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\StatamicHelpers\Facades\Helper;

class RefreshEntryCache
{
    public function handle(object $event): void
    {
        $slug = $event->entry->slug;
        $collection = $event->entry->collection->handle;

        $languages = config('translatable.languages') ?: ['default' => []];
        $currentLocale = app()->getLocale();

        foreach ($languages as $language => $languageParams) {
            app()->setLocale($language);

            $key = $collection.'.'.$slug.'.'.$language;

            Cache::forget($key);

            Cache::rememberForever($key, function () use ($slug, $collection) {
                return Helper::entry(collection: $collection, slug: $slug);
            });
        }

        app()->setLocale($currentLocale);
    }
}
