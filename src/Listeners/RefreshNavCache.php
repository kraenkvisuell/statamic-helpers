<?php

namespace Kraenkvisuell\StatamicHelpers\Listeners;

use Illuminate\Support\Facades\Cache;
use Kraenkvisuell\StatamicHelpers\Facades\Helper;

class RefreshNavCache
{
    public function handle(object $event): void
    {
        $languages = config('translatable.languages') ?: ['default' => []];
        $currentLocale = app()->getLocale();

        foreach ($languages as $language => $languageParams) {
            app()->setLocale($language);

            Cache::forget('all_navs.'.$language);

            Cache::rememberForever('all_navs.'.$language, function () {
                return Helper::allNavs();
            });
        }

        app()->setLocale($currentLocale);
    }
}
