<?php

use Kraenkvisuell\StatamicHelpers\Facades\Helper;

function statamic_entry(
    string $slug = 'home',
    string $collection = 'pages',
    string $site = '',
    array $select = [],
) {
    return Helper::entry(
        $slug,
        $collection,
        $site,
        $select
    );
}

function statamic_nav(
    string $slug = '',
    int $maxDepth = 0,
    array $select = []
) {
    return Helper::hav(
        $slug,
        $maxDepth,
        $select
    );
}

function statamic_global(
    string $key = '',
    string $site = ''
) {
    return Helper::global(
        $key,
        $site
    );
}

function statamic_asset(
    string $path = '',
    string $disk = ''
) {
    return Helper::asset(
        $path,
        $disk
    );
}
