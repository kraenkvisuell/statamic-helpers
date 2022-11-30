<?php

namespace Kraenkvisuell\StatamicHelpers;

use Statamic\Statamic;
use Statamic\Facades\Site;
use Statamic\Facades\Entry;
use Statamic\Facades\GlobalSet;
use Illuminate\Support\Facades\Storage;

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
        string $key = '',
        string $site = ''
    ) {
        $site = $site ?: Site::current()->handle();

        $keyArr = explode(':', $key);

        $handle = count($keyArr) > 1 ? $keyArr[0] : 'globals';
        $field = $keyArr[count($keyArr) - 1];

        return GlobalSet::findByHandle($handle)
            ->in($site)
            ->get($field);
    }

    public function asset(
        string $path = '',
        string $disk = ''
    ) {
        $disk = $disk ?: 'assets';
        
        $cdn = config('filesystems.disks.'.$disk.'.cdn');
        
        if ($cdn) {
            return $cdn.'/'.$path;
        }

        return Storage::disk($disk)->url($path);
    }   
}
