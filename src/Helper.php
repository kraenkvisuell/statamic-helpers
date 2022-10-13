<?php

namespace Kraenkvisuell\StatamicHelpers;

use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Statamic;

class Helper
{
    public static function getEntry(
        $slug = '',
        $collection = '',
        $publishedOnly = true,
        $site = ''
    ) {
        $site = $site ?: Site::current()->handle();

        $builder = Entry::query()
                ->where('collection', $collection)
                ->where('slug', $slug)
                ->where('site', $site);

        if ($publishedOnly) {
            $builder->where('published', true);
        }

        $entry = $builder->first();

        if (! $entry) {
            return null;
        }

        return $entry;
    }

    public static function getNav(
        $handle = '',
        $site = ''
    ) {
        $handle = trim($handle);
        $nav = [];

        ray('foo');

        ray(Statamic::tag('nav:'.$handle));

        foreach (Statamic::tag('nav:'.$handle) ?: [] as $level) {
        }

        return $nav;
    }
}
