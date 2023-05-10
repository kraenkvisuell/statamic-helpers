<?php

namespace Kraenkvisuell\StatamicHelpers\Console;


use Statamic\Facades\Nav;
use Statamic\Facades\Site;
use Illuminate\Support\Str;
use Statamic\Facades\Entry;
use Illuminate\Console\Command;

class GenerateDummySite extends Command
{
    public $signature = 'kv:dummy-site';

    public $pageCount = 30;
    public $sites;

    public function __construct()
    {
        parent::__construct();
        $this->sites = Site::all()->where('handle', '!=', 'default')->pluck('handle')->toArray();
    }

    public function handle()
    {
        $message = 'ACHTUNG! Alle bestehenden Seiten, Posts und sonstige Einträge werden vorher gelöscht!';

        if (! $this->confirm($message.' Wirklich weitermachen? [y|N]')) {
            return $this->info('Vorgang abgebrochen.');
        }

        Nav::findByHandle('main_nav')?->delete();
        Nav::findByHandle('footer_nav')?->delete();

        Entry::query()->where('collection', 'pages')->get()->each->deleteDescendants(); 
        Entry::query()->where('collection', 'pages')->get()->each->delete();
 
        Entry::query()->where('collection', 'posts')->get()->each->deleteDescendants();
        Entry::query()->where('collection', 'posts')->get()->each->delete();

        $this->createHomepage();
        $this->createPages();
        $this->createMainMenu();
        $this->createFooterMenu();
        
        $this->call('cache:clear');

        $this->info('Dummy-Site generiert');
    }

    protected function createHomepage()
    {
        $entry = Entry::make()->collection('pages')->slug('home');
        $entry->set('title', 'Home');
        $entry->set('main_replicator', [
            [
                'id' => Str::random(8),
                'headline' => 'Home',
                'headline_level' => 'h2',
                'kind' => 'random_url',
                'type' => 'image',
                'enabled' => true,
            ],
        ]);

        $entry->save();

        foreach($this->sites as $site) {
            $siteEntry = Entry::make()
                ->collection('pages')
                ->locale($site)
                ->slug('home')
                ->origin($entry->id());

            $siteEntry->save();  
        }
    }
    
    protected function createPages()
    {
        
        for($pageIndex = 0; $pageIndex < $this->pageCount; $pageIndex++) {
            $title = ucfirst(fake()->words(rand(2, 3), true));
            $slug = Str::slug($title);

            $entry = Entry::make()->collection('pages')->slug($slug);
            $entry->set('title', $title);
            $entry->set('main_replicator', [
                [
                    'id' => Str::random(8),
                    'headline' => ucfirst(fake()->words(rand(2, 4), true)),
                    'headline_level' => 'h2',
                    'kind' => 'random_url',
                    'type' => 'image',
                    'enabled' => true,
                ],
            ]);

            $entry->save();

            foreach($this->sites as $site) {
                $siteEntry = Entry::make()
                    ->collection('pages')
                    ->locale($site)
                    ->slug($slug)
                    ->origin($entry->id());

                $siteEntry->save();  
            }
        }
    }

    protected function createMainMenu()
    {
        $pagesForNav = [
            'default' => [],
        ];

        foreach($this->sites as $site) {
            $pagesForNav[$site] = [];
        }

        foreach($this->getPagesForMainMenu() as $pageIndex => $page) {
            $data = [
                'id' => Str::uuid()->toString(),
                'entry' => $page->id(),
            ];

            if ($pageIndex > 0 && $pageIndex < 3) {
                $data['children'] = $this->getSubPagesForMainMenu($pageIndex);
            }

            $pagesForNav['default'][] = $data;

            foreach($this->sites as $site) {
                $sitePage = Entry::query()
                    ->where('site', $site)
                    ->where('origin', $page->id())
                    ->first();

                $subData = [
                    'id' => Str::uuid()->toString(),
                    'entry' => $sitePage->id(),
                ];

                if ($pageIndex > 0 && $pageIndex < 3) {
                    $subData['children'] = $this->getSubPagesForMainMenu($pageIndex, $site);
                }

                $pagesForNav[$site][] = $subData;
            }
        }

        $nav = Nav::make()
            ->title('Haupt-Navigation')
            ->handle('main_nav')
            ->collections(['pages']);

        $nav->makeTree('default')->save();

        $nav->in('default')->tree($pagesForNav['default'])->save();
        
        foreach($this->sites as $site) {
            $nav->makeTree($site)->save();

            $nav->in($site)->tree($pagesForNav[$site])->save();
        }

        $nav->save();
    }

    protected function getPagesForMainMenu()
    {
        return Entry::query()
            ->where('collection', 'pages')
            ->where('site', 'default')
            ->where('slug', '!=', 'home')
            ->get()
            ->take(7);
    }

    protected function createFooterMenu()
    {
        $pagesForNav = [
            'default' => [],
        ];

        foreach($this->sites as $site) {
            $pagesForNav[$site] = [];
        }

        $pageIndex = 0;
        foreach($this->getPagesForFooterMenu() as $page) {
            $data = [
                'id' => Str::uuid()->toString(),
                'entry' => $page->id(),
            ];

            ray($pageIndex);
            if ($pageIndex == 0) {
                $page->set('title', 'Kontakt')->slug('kontakt');

                foreach(Entry::query()->where('origin', $page->id())->get() as $sitePage) {
                    $sitePage->set('title', 'Contact')->slug('contact');
                    $sitePage->save();
                }
            } elseif ($pageIndex == 1) {
                $page->set('title', 'Impressum')->slug('impressum');
                
                foreach(Entry::query()->where('origin', $page->id())->get() as $sitePage) {
                    $sitePage->set('title', 'Imprint')->slug('imprint');
                    $sitePage->save();
                }
            } elseif ($pageIndex == 2) {
                $page->set('title', 'Datenschutz')->slug('datenschutz');

                foreach(Entry::query()->where('origin', $page->id())->get() as $sitePage) {
                    $sitePage->set('title', 'Privacy Policy')->slug('privacy-policy');
                    $sitePage->save();
                }
            }

            $page->save();

            $pagesForNav['default'][] = $data;

            foreach($this->sites as $site) {
                $sitePage = Entry::query()
                    ->where('site', $site)
                    ->where('origin', $page->id())
                    ->first();

                $subData = [
                    'id' => Str::uuid()->toString(),
                    'entry' => $sitePage->id(),
                ];

                $pagesForNav[$site][] = $subData;
            }

            $pageIndex++;
        }

        $nav = Nav::make()
            ->title('Footer-Navigation')
            ->handle('footer_nav')
            ->collections(['pages']);

        $nav->makeTree('default')->save();

        $nav->in('default')->tree($pagesForNav['default'])->save();
        
        foreach($this->sites as $site) {
            $nav->makeTree($site)->save();

            $nav->in($site)->tree($pagesForNav[$site])->save();
        }

        $nav->save();
    }

    protected function getSubPagesForMainMenu($pageIndex, $site = 'default')
    {
        $query = Entry::query()
            ->where('collection', 'pages')
            ->where('site', $site)
            ->where('slug', '!=', 'home');

        $slice = ($pageIndex * 7) + 1;

        return $query->get()
            ->slice($slice, 3)
            ->map(function($page) {
                return [
                    'id' => Str::uuid()->toString(),
                    'entry' => $page->id(),
                ];
            })
            ->all();
    }

    protected function getPagesForFooterMenu()
    {
        return Entry::query()
            ->where('collection', 'pages')
            ->where('site', 'default')
            ->where('slug', '!=', 'home')
            ->get()
            ->take(-3);
    }
}
