<?php

namespace Kraenkvisuell\StatamicHelpers\Services;

use Statamic\Facades\Entry;
use Statamic\Facades\Site;

class QueryService
{
    public static function findBySlug(
        string $slug = '',
        string $collection = '',
        string $site = '',
        bool $publishedOnly = true
    ) {
        $site = $site ?: Site::current()->handle();

        //ray($site);

        $entry = Entry::query()
                ->where('collection', $collection)
                ->where('slug', $slug)
                ->where('site', $site)
                ->first();

        if (! $entry) {
            return null;
        }

        // $entry = $entry->toAugmentedArray();

        // if (isset($entry['published']) && $publishedOnly && ! $entry['published']) {
        //     return null;
        // }

        return $entry;
    }
}
