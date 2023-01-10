<?php

use Kraenkvisuell\StatamicHelpers\Facades\Helper;

function statamic_entries(
    $collection = 'pages',
    $site = '',
    $select = [],
) {
    return Helper::entries(
        $collection,
        $site,
        $select
    );
}

function statamic_entry(
    $slug = 'home',
    $collection = 'pages',
    $site = '',
    $select = [],
) {
    return Helper::entry(
        $slug,
        $collection,
        $site,
        $select
    );
}

function statamic_nav(
    $slug = '',
    $maxDepth = 0,
    $select = []
) {
    return Helper::hav(
        $slug,
        $maxDepth,
        $select
    );
}

function statamic_global(
    $key = '',
    $site = ''
) {
    return Helper::global(
        $key,
        $site
    );
}

function statamic_asset(
    $path = '',
    $disk = ''
) {
    return Helper::asset(
        $path,
        $disk
    );
}

function statamic_glide(
    $path = '',
    $disk = ''
) {
    return Helper::glide(
        $path,
        $disk
    );
}
