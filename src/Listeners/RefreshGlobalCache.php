<?php

namespace Kraenkvisuell\StatamicHelpers\Listeners;

use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\StatamicHelpers\Facades\Helper;

class RefreshGlobalCache
{
    public function handle(object $event): void
    {
        $languages = config('translatable.languages') ?: ['default' => []];
        $sites = config('statamic.sites.sites');
        //$handle = $event->globals?->handle();

        foreach ($languages as $language => $languageParams) {
            foreach ($sites as $site => $siteParams) {
                Cache::forget('all_globals.'.$language.'.'.$site);

                Cache::rememberForever('all_globals.'.$language.'.'.$site, function () use ($site) {
                    return Helper::allGlobals(site: $site);
                });
            }
        }
    }
}
