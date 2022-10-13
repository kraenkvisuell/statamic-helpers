<?php

namespace Kraenkvisuell\StatamicHelpers;

use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Statamic;

class Helper
{
    public function entry(
        string $slug = '',
        string $collection = 'pages',
        bool $publishedOnly = true,
        string $site = '',
        array $select = [],
    ) {
        $site = $site ?: Site::current()->handle();

        $select = $select ?: ['blueprint', 'content', 'title'];

        $builder = Entry::query()
            ->where('collection', $collection)
            ->where('slug', $slug)
            ->where('site', $site)
            ->select($select);

        if ($publishedOnly) {
            $builder->where('published', true);
        }

        $entry = $builder->first();

        if (! $entry) {
            return null;
        }

        return $entry;
    }

    public function nav(
        string $slug = '',
        string $site = '',
        int $maxDepth = 0,
        array $select = []
    ) {
        $site = $site ?: Site::current()->handle();

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

    protected function getNavLevel($level)
    {
        //ray($level);

        return $level;
    }
}
