<?php

namespace Kraenkvisuell\StatamicHelpers;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Statamic\Facades\Entry;
use Statamic\Facades\Form;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Facades\Structure;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Statamic;

class Helper
{
    protected $forbidden = [
        'amp_url',
        'api_url',
        'edit_url',
        'last_modified_instance',
        'last_modified',
        'mount',
        'private',
        'published',
        'updated_by',
    ];

    public $cachedTaxonomies = [];

    public $cachedTerms = [];

    public function isPreview()
    {
        return request()->get('preview') || request()->get('live-preview');
    }

    public function entries(
        $collection = 'pages',
        $site = '',
        array $select = [],
        bool $hideInternals = true,
        $orderBy = '',
        $orderDirection = 'asc',
    ) {
        $site = $site ?: Site::current()->handle();

        $query = Entry::query()
            ->where('collection', $collection)
            ->where('site', $site)
            ->where('published', true);

        if ($orderBy) {
            $query->orderBy($orderBy, $orderDirection);
        }

        if ($select) {
            $select = array_merge(
                ['collection', 'blueprint', 'is_entry'],
                $select
            );
            $query->select($select);
        }

        $entries = $query->get();

        if (! $entries) {
            return [];
        }

        if (! $hideInternals) {
            return $entries;
        }

        $cleanedEntries = [];

        foreach ($entries as $entry) {
            $cleaned = [];

            foreach ($entry->toArray() as $rawKey => $rawValue) {
                if (! in_array($rawKey, $this->forbidden)) {
                    if ($taxonomy = Taxonomy::findByHandle($rawKey)) {
                        $cleaned[$rawKey] = $this->hydratedTaxonomies($rawKey, $rawValue);

                    } else {
                        $cleaned[$rawKey] = $this->cleaned($rawValue);
                    }
                }
            }

            $cleanedEntries[] = $cleaned;
        }

        $nav = Statamic::tag('nav:collection:'.$collection)->fetch();

        if ($nav) {
            $sortedEntries = [];

            foreach ($nav as $navItem) {
                $sortedEntries[] = collect($cleanedEntries)
                    ->firstWhere('id', $navItem['entry_id']->raw());
            }

            return $sortedEntries;
        }

        return $cleanedEntries;
    }

    public function cachedEntry($collection = 'pages', $slug = 'home')
    {
        $key = $collection.'.'.$slug.'.'.app()->getLocale();

        Cache::forget($key);

        return Cache::rememberForever($key, function () use ($collection, $slug) {
            return $this->entry(collection: $collection, slug: $slug);
        });
    }

    public function entry(
        $id = null,
        $originId = null,
        $slug = 'home',
        $collection = 'pages',
        $site = '',
        $select = [],
        $hideInternals = true,
        $withChildren = false,
        $flat = false,
        $isHome = false,
    ) {
        $site = $site ?: Site::current()->handle();

        if ($id) {
            $entry = Entry::find($id);

            if ($flat) {
                return $entry;
            }
        } elseif ($isHome) {
            $entry = Entry::query()
                ->where('collection', $collection)
                ->where('is_home', true)
                ->where('site', $site)
                ->where('published', true)
                ->first();

            if ($flat) {
                return $entry;
            }
        } else {
            $query = Entry::query()
                ->where('collection', $collection)
                ->where('site', $site)
                ->where('published', true);

            if ($originId) {
                $query->where('origin', $originId);
            } else {
                $query->where('slug', $slug);
            }

            if ($select) {
                $query->select($select);
            }

            $entry = $query->first();

            if ($flat) {
                return $entry;
            }
        }

        if (! $entry) {
            return [];
        }

        return $this->augmentEntry($entry, $hideInternals, $withChildren);
    }

    public function augmentEntry($entry, $hideInternals = true, $withChildren = false)
    {
        if ($hideInternals) {
            $cleaned = [];
            ray($entry->toAugmentedArray());
            foreach ($entry->toAugmentedArray() as $key => $value) {
                if (! in_array($key, $this->forbidden)) {
                    $cleaned[$key] = $this->cleaned($value);

                    //                    if (config('statamic-helpers.with_shop_addon') && Str::contains($key, 'linked_product') && isset($value[0])) {
                    //                        $cleaned[$key] = $this->getProductResource($value[0]);
                    //                    } elseif (Str::contains($key, 'linked_page') && isset($value[0])) {
                    //                        $cleaned[$key] = $this->entry(id: $value[0]);
                    //
                    //                    } else {
                    //                        $cleaned[$key] = $this->cleaned($value);
                    //                    }
                }
            }
            $entry = $cleaned;
        }

        if ($withChildren) {
            $entry['children'] = $this->childrenOf($entry);
        }

        return $entry;
    }

    public function childrenOf($entry)
    {
        ray($entry);
        $children = [];
        $nav = Statamic::tag('nav:collection:'.$entry['collection']['handle'])
            ->params([
                'from' => $entry['url'],
            ])
            ->fetch();

        if ($nav) {
            foreach ($nav as $navItem) {
                $children[] = $this->entry(
                    id: $navItem['id']
                );
            }
        }

        return $children;
    }

    public function nav(
        $slug = 'bar',
        $maxDepth = 0,
        $select = []
    ) {
        $select = array_unique(
            array_merge(['title', 'slug', 'is_current', 'url', 'id', 'entry_id'], $select)
        );

        $slug = trim($slug);

        $params = [
            'select' => implode('|', array_map('trim', $select)),
        ];

        if ($maxDepth) {
            $params['max_depth'] = $maxDepth;
        }

        $nav = Statamic::tag('nav:'.$slug)
            ->params($params)
            ->fetch();

        return $nav;
    }

    public function allNavs($select = [])
    {
        $navs = [];
        $navs['collection'] = [];

        foreach (Structure::all() as $navTag) {
            $handle = $navTag->handle;

            if (stristr(get_class($navTag), 'CollectionStructure')) {
                $navs['collection'][$handle] = $this->nav(slug: 'collection:'.$handle, select: $select);
            } else {
                $navs[$handle] = $this->nav(slug: $handle, select: $select);
            }
        }

        return $navs;
    }

    public function cachedAllNavs($language = 'default')
    {
        return Cache::rememberForever('all_navs.'.$language, function () {
            return $this->allNavs();
        });
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
            if (! in_array($rawKey, $this->forbidden)) {
                $cleaned[$rawKey] = $this->cleaned($rawValue);
            }
        }

        return $cleaned;
    }

    public function allGlobals($site = '')
    {
        $site = $site ?: Site::current()->handle();

        $all = GlobalSet::all();
        $globals = [];

        foreach ($all as $set) {
            $globals[$set->handle()] = $this->globals($set->handle(), $site);
        }

        return $globals;
    }

    public function cachedAllGlobals($language = 'default', $site = '')
    {
        $site = $site ?: Site::current()->handle();

        //Cache::forget('all_globals.'.$language.'.'.$site);
        return Cache::rememberForever('all_globals.'.$language.'.'.$site, function () use ($site) {
            return $this->allGlobals(site: $site);
        });
    }

    public function allForms()
    {
        $all = Form::all();

        $forms = [];

        foreach ($all as $form) {
            $fetched = Statamic::tag('form:'.$form->handle())->fetch();

            $forms[$form->handle()] = [
                'fields' => $form->fields,
                'action' => $fetched['attrs']['action'],
            ];
        }

        return $forms;
    }

    protected function hydratedTaxonomies(
        $handle = '',
        $slugs = []
    ) {
        sort($slugs);
        $cacheKey = $handle.'_'.implode('_', $slugs);

        if (isset($this->cachedTaxonomies[$cacheKey])) {
            return $this->cachedTaxonomies[$cacheKey];
        }

        $taxonomies = Term::query()
            ->where('taxonomy', $handle)
            ->get()
            ->filter(function ($term) use ($slugs) {
                return in_array($term->slug(), $slugs);
            })
            ->map(function ($term, $handle) {
                $cacheKey = $handle.'_'.$term->slug();
                if (! isset($this->cachedTerms[$cacheKey])) {
                    $term = $term->toArray();

                    $this->cachedTerms[$cacheKey] = [
                        'id' => $term['id'],
                        'slug' => $term['slug'],
                        'title' => $term['title'],
                    ];
                }

                return $this->cachedTerms[$cacheKey];
            });

        $this->cachedTaxonomies[$cacheKey] = $taxonomies->toArray();

        return $this->cachedTaxonomies[$cacheKey];
    }

    protected function checkIfTaxonomies($list = [])
    {
        $handles = Taxonomy::handles();
        foreach ($handles as $handle) {
            if ($taxonomies = $this->hydratedTaxonomies($handle, $list)) {
                return $taxonomies;
            }
        }

        return null;
    }

    protected function checkIfForm($list = [])
    {
        if (count($list) === 1 && is_string($list[0])) {
            try {
                if ($form = Statamic::tag('form:'.$list[0])->fetch()) {
                    return $form;
                }
            } catch (Exception $e) {
                //
            }
        }

        return $list;
    }

    public function generatePresets($rawValue, $originalUrl)
    {
        $presets = [];

        $presetDisk = config('statamic-helpers.preset_disk') ?: 'presets';

        $cdn = config('filesystems.disks.'.$presetDisk.'.cdn');
        $root = config('filesystems.disks.'.$presetDisk.'.root');

        foreach (config('statamic-helpers.presets') ?: [] as $presetKey => $preset) {
            if ($rawValue['mime_type'] != 'image/jpeg' && $rawValue['mime_type'] != 'image/png') {
                $presets[$presetKey] = $originalUrl;
            } elseif ($cdn) {
                $presets[$presetKey] = $cdn.'/'.($root ? $root.'/' : '').$presetKey.'/'.$rawValue['path'];
            } else {
                $presets[$presetKey] = Storage::disk($presetDisk)->url($rawValue['path']);
            }
        }

        return $presets;
    }

    public function asset(
        $path = '',
        $disk = '',
        $useCdn = true,
    ) {
        if (stristr($path, '::')) {
            $disk = $disk ?: Str::beforeLast($path, '::');
            $path = Str::afterLast($path, '::');
        }
        $disk = $disk ?: 'assets';

        $cdn = config('filesystems.disks.'.$disk.'.cdn');
        $root = config('filesystems.disks.'.$disk.'.root');

        if ($useCdn && $cdn) {
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
            foreach ($rawValue as $key => $value) {
                if (! in_array($key, $this->forbidden)) {
                    if (is_array($value)) {
                        if (
                            array_is_list($value)
                            && isset($value[0])
                            && is_string($value[0])
                        ) {
                            if ($taxonomies = $this->checkIfTaxonomies($value)) {
                                $cleanedValue[$key] = $taxonomies;
                            } elseif (stristr($key, 'form') && $form = $this->checkIfForm($value)) {
                                $cleanedValue[$key] = $form;
                            } elseif (Str::contains($key, 'linked_page') && isset($value[0])) {
                                $cleanedValue[$key] = $this->entry(id: $value[0]);
                            }

                        } elseif (config('statamic-helpers.with_shop_addon') && Str::contains($key, 'linked_product') && isset($value[0])) {
                            $cleanedValue[$key] = $this->getProductResource($value[0]);
                        } else {
                            $cleanedValue[$key] = $this->cleaned($value);
                        }

                    } else {

                        if ($key == 'url' && $isAsset && $path && ! $value) {
                            $disk = $rawValue['container']['disk'] ?? '';
                            $cleanedValue[$key] = Helper::asset($path, $disk);
                            $cleanedValue['presets'] = $this->generatePresets($rawValue, $cleanedValue[$key]);
                        } else {
                            $cleanedValue[$key] = $this->cleanedValue($value, $key);
                        }
                    }
                }
            }
        }


        return $cleanedValue;
    }

    protected function cleanedValue($value, $key)
    {
        if (
            is_string($value)
            && $key != 'url'
            && $key != 'id'
            && ! stristr($key, '_id')
            && strlen($value) > 9
            && Str::substrCount($value, '-', 2) == 4
        ) {

            $entry = $this->entry(
                id: $value,
            );

            if ($entry) {
                return $entry;
            }
        }

        return $value;
    }

    public function getProductResource($value): \App\Http\Resources\ProductResource
    {
        $product = \App\Models\Product::with('skus.colors')->findOrNew($value);

        return new \App\Http\Resources\ProductResource($product);
    }
}
