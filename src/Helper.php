<?php

namespace Kraenkvisuell\StatamicHelpers;

use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Statamic;

class Helper
{
    public function entry(
        string $slug = 'home',
        string $collection = 'pages',
        string $site = '',
        array $select = [],
    ) {
        $site = $site ?: Site::current()->handle();

        $select = $select ?: ['blueprint', 'content', 'title'];

        return Entry::query()
            ->where('collection', $collection)
            ->where('slug', $slug)
            ->where('site', $site)
            ->where('published', true)
            ->select($select)
            ->first();
    }

    public function nav(
        string $slug = '',
        int $maxDepth = 0,
        array $select = []
    ) {
        $select = array_unique(
            array_merge(['title', 'is_current', 'url'], $select)
        );

        $slug = trim($slug);

        $params = [
            'select' => implode('|', array_map('trim', $select)),
        ];

        if ($maxDepth) {
            $params['max_depth'] = $maxDepth;
        }

        return Statamic::tag('nav:'.$slug)
            ->params($params)
            ->fetch();
    }

    public function global(
        string $slug = '',
        int $maxDepth = 0,
        array $select = []
    ) {
        $select = array_unique(
            array_merge(['title', 'is_current', 'url'], $select)
        );

        $slug = trim($slug);

        $params = [
            'select' => implode('|', array_map('trim', $select)),
        ];

        if ($maxDepth) {
            $params['max_depth'] = $maxDepth;
        }

        return Statamic::tag('nav:'.$slug)
            ->params($params)
            ->fetch();
    }
}
