<?php

namespace Kraenkvisuell\StatamicHelpers;

use Statamic\Facades\Entry;
use Statamic\Facades\Site;

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
}
