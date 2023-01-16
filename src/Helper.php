<?php

namespace Kraenkvisuell\StatamicHelpers;

use Statamic\Statamic;
use Statamic\Facades\Site;
use Illuminate\Support\Str;
use Statamic\Facades\Entry;
use Statamic\Facades\GlobalSet;
use Illuminate\Support\Facades\Storage;

class Helper
{
    protected $forbidden = [
        'amp_url',
        'api_url',
        'edit_url',
        'last_modified_instance',
        'last_modified', 
        'mount',
        'origin_id',
        'private',
        'published',
        'updated_by',
    ];

    public function isPreview()
    {
        return request()->get('preview') || request()->get('live-preview');
    }

    public function entries(
        $collection = 'pages',
        $site = '',
        array $select = [],
        bool $hideInternals = true,
    ) {
        $site = $site ?: Site::current()->handle();

        $query = Entry::query()
            ->where('collection', $collection)
            ->where('site', $site)
            ->where('published', true);

        if ($select) {
            $query->select($select);
        }

        $entries = $query->get();

        if (!$entries) {
            return [];
        }

        if (!$hideInternals) {
            return $entries;
        }

        $cleanedEntries = [];

       foreach($entries as $entry) {
            $cleaned = [];

            foreach ($entry->toArray() as $rawKey => $rawValue) {
                if (!in_array($rawKey, $this->forbidden)) {
                    $cleaned[$rawKey] = $this->cleaned($rawValue);
                }
            }
            
            $cleanedEntries[] = $cleaned;
        }

        $nav = Statamic::tag('nav:collection:'.$collection)->fetch();

        if ($nav) {
            $sortedEntries = [];

            foreach($nav as $navItem) {
                $sortedEntries[] = collect($cleanedEntries)
                    ->firstWhere('id', $navItem['entry_id']->raw());
            }

            return $sortedEntries;
        }


        return $cleanedEntries;
    }

    public function entry(
        $id = null,
        $slug = 'home',
        $collection = 'pages',
        $site = '',
        $select = [],
        $hideInternals = true,
    ) {
        $site = $site ?: Site::current()->handle();

        if ($id) {
            $entry = Entry::find($id);
        } else {
            $query = Entry::query()
                ->where('collection', $collection)
                ->where('slug', $slug)
                ->where('site', $site)
                ->where('published', true);

            if ($select) {
                $query->select($select);
            }

            $entry = $query->first();
        }
        
        if (!$entry) {
            return [];
        }

        if ($hideInternals) {
            $cleaned = [];

            foreach ($entry->toArray() as $rawKey => $rawValue) {
                if (!in_array($rawKey, $this->forbidden)) {
                    $cleaned[$rawKey] = $this->cleaned($rawValue);
                }
            }
            
            return $cleaned;
        }

        return $entry;
    }

    public function childrenOf($entry) {
        $children = [];
        ray($entry['collection']['handle']);

        $nav = Statamic::tag('nav:collection:'.$entry['collection']['handle'])
            ->params([
                'from' => $entry['url']
            ])
            ->fetch();

        if ($nav) {
            foreach($nav as $navItem) {
                $children[] = $this->entry(
                    id: $navItem['id']
                );
            }
        }

        return $children;
    }

    public function nav(
        $slug = '',
        $maxDepth = 0,
        $select = []
    ) {
        $select = array_unique(
            array_merge(['title', 'is_current', 'url', 'id', 'entry_id'], $select)
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

    public function allNavs() {
        $navs = [];

        $navigationFiles = scandir(base_path('content/navigation')) ?: [];
        foreach($navigationFiles as $navigationFile) {
            if (!Str::startsWith($navigationFile, '.') && Str::endsWith($navigationFile, '.yaml')) {
                $handle = Str::beforeLast($navigationFile, '.');
                $navs[$handle] = $this->nav($handle);
            }
        }
        
        if (!is_dir(base_path('content/trees/collections'))) {
            return $navs;
        }

        $navs['collection'] = [];
        $collectionFiles = scandir(base_path('content/trees/collections')) ?: [];
        foreach($collectionFiles as $collectionFile) {
            if (!Str::startsWith($collectionFile, '.') && Str::endsWith($collectionFile, '.yaml')) {
                $handle = Str::beforeLast($collectionFile, '.');
                $navs['collection'][$handle] = $this->nav('collection:'.$handle);
            }
        }

        return $navs;
    }

    public function global(
        $key = '',
        $site = ''
    ) {
        $site = $site ?: Site::current()->handle();

        $keyArr = explode(':', $key);
        if (count($keyArr) == 1) {
            $keyArr = explode('.', $key);
        }

        $handle = count($keyArr) > 1 ? $keyArr[0] : 'globals';
        $field = $keyArr[count($keyArr) - 1];

        return GlobalSet::findByHandle($handle)
            ->in($site)
            ->get($field);
    }

    public function globals(
        $handle = '',
        $site = ''
    ) {
        $site = $site ?: Site::current()->handle();

        $raw = GlobalSet::findByHandle($handle)
            ->in($site)
            ->toArray();

        $cleaned = [];

        foreach ($raw as $rawKey => $rawValue) {
            if (!in_array($rawKey, $this->forbidden)) {
                $cleaned[$rawKey] = $this->cleaned($rawValue);
            }
        }
    
        return $cleaned;
    }

    public function allGlobals($site = '') {
        $site = $site ?: Site::current()->handle();

        $all = GlobalSet::all();
        $globals = [];

        foreach($all as $set) {
            $globals[$set->handle()] = $this->globals($set->handle(), $site);
        }
        
        return $globals;
    }

    public function asset(
        $path = '',
        $disk = ''
    ) {
        if (stristr($path, '::')) {
            $disk = $disk ?: Str::beforeLast($path, '::');
            $path = Str::afterLast($path, '::');
        }
        $disk = $disk ?: 'assets';
        
        $cdn = config('filesystems.disks.'.$disk.'.cdn');
        $root = config('filesystems.disks.'.$disk.'.root');
        
        if ($cdn) {
            return $cdn.'/'.($root ? $root.'/' : '').$path;
        }

        return Storage::disk($disk)->url($path);
    }   

    public function glide(
        $path = '',
        $disk = ''
    ) {
        $url = $this->asset($path, $disk);

        $images = Statamic::tag('glide:generate')
            ->src($url)
            ->width(1500)
            ->fit('max')
            ->quality(90);

            foreach ($images as $image) {
                return $image['url'] ?? '';
            }
    }   

    protected function cleaned($rawValue)
    {
        $cleanedValue = $rawValue;

        if (is_array($rawValue)) {
            $cleanedValue = [];
            $isAsset = $rawValue['is_asset'] ?? false;
            $path = $rawValue['path'] ?? '';

            foreach($rawValue as $key => $value) {
                if (!in_array($key, $this->forbidden)) {
                    if (is_array($value)) {
                        $cleanedValue[$key] = $this->cleaned($value);
                    } else {
                        $cleanedValue[$key] = $value;
                        if ($key == 'url' && $isAsset && $path && !$value) {
                            $disk = $rawValue['container']['disk'] ?? '';
                            $cleanedValue[$key] = Helper::asset($path, $disk);
                        }
                    }
                }
            }
        }
        
        return $cleanedValue;
    }
}
