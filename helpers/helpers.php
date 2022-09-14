<?php

use Kraenkvisuell\StatamicHelpers\Services\QueryService;
use Statamic\Facades\Site;

function statamic_published_entry(
    $slug,
    $collection
) {
    $site = Site::current()->handle();

    return QueryService::findBySlug(
        slug: $slug,
        collection: $collection,
        site: $site
    );
}

function statamic_entry(
    $slug,
    $collection
) {
    return QueryService::findBySlug(
        slug: $slug,
        collection: $collection,
        publishedOnly: false
    );
}
