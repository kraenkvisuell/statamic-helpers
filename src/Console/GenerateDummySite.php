<?php

namespace Kraenkvisuell\StatamicHelpers\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Statamic\Facades\Entry;
use Statamic\Facades\Nav;
use Statamic\Facades\Site;

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
        $entry->set('is_home', true);
        $entry->set('main_replicator', $this->generateMainContent());

        $entry->save();

        foreach ($this->sites as $site) {
            $siteEntry = Entry::make()
                ->collection('pages')
                ->locale($site)
                ->slug('home')
                ->origin($entry->id());

            $siteEntry->set('is_home', true);
            $siteEntry->save();
        }
    }

    protected function createPages()
    {

        for ($pageIndex = 0; $pageIndex < $this->pageCount; $pageIndex++) {
            $title = ucfirst(fake()->words(rand(2, 3), true));
            $slug = Str::slug($title);

            $entry = Entry::make()->collection('pages')->slug($slug);
            $entry->set('title', $title);
            $entry->set('main_replicator', $this->generateMainContent());

            $entry->save();

            foreach ($this->sites as $site) {
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

        foreach ($this->sites as $site) {
            $pagesForNav[$site] = [];
        }

        foreach ($this->getPagesForMainMenu() as $pageIndex => $page) {
            $data = [
                'id' => Str::uuid()->toString(),
            ];

            if ($pageIndex > 0 && $pageIndex < 3) {
                $data['title'] = ucfirst(fake()->words(rand(2, 3), true));
                $data['children'] = $this->getSubPagesForMainMenu($pageIndex);
            } else {
                $data['entry'] = $page->id();
            }

            $pagesForNav['default'][] = $data;

            foreach ($this->sites as $site) {
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

        foreach ($this->sites as $site) {
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

        foreach ($this->sites as $site) {
            $pagesForNav[$site] = [];
        }

        $pageIndex = 0;
        foreach ($this->getPagesForFooterMenu() as $page) {
            $data = [
                'id' => Str::uuid()->toString(),
                'entry' => $page->id(),
            ];

            if ($pageIndex == 0) {
                $page->set('title', 'Kontakt')->slug('kontakt');

                foreach (Entry::query()->where('origin', $page->id())->get() as $sitePage) {
                    $sitePage->set('title', 'Contact')->slug('contact');
                    $sitePage->save();
                }

                $page->save();
            } elseif ($pageIndex == 1) {
                $page->set('title', 'Impressum')->slug('impressum');

                foreach (Entry::query()->where('origin', $page->id())->get() as $sitePage) {
                    $sitePage->set('title', 'Imprint')->slug('imprint');
                    $sitePage->save();
                }

                $page->save();
            } elseif ($pageIndex == 2) {
                $page->set('title', 'Datenschutz')->slug('datenschutz');

                foreach (Entry::query()->where('origin', $page->id())->get() as $sitePage) {
                    $sitePage->set('title', 'Privacy Policy')->slug('privacy-policy');
                    $sitePage->save();
                }

                $page->save();
            }

            $pagesForNav['default'][] = $data;

            foreach ($this->sites as $site) {
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

        foreach ($this->sites as $site) {
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
            ->map(function ($page) {
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

    protected function generateMainContent()
    {
        $elements = [
            'image' => [
                'count' => rand(1, 4),
            ],
            'text' => [
                'count' => rand(1, 4),
            ],
            'video' => [
                'count' => rand(1, 2),
            ],
        ];

        $content = [];

        foreach ($elements as $handle => $params) {
            for ($n = 0; $n < $params['count']; $n++) {
                $item = [
                    'id' => Str::random(8),
                    'enabled' => true,
                    'type' => $handle,
                ];

                if (rand(0, 10) >= 3) {
                    $item['headline'] = ucfirst(fake()->words(rand(2, 4), true));
                    $item['headline_level'] = 'h2';
                }

                if (($handle == 'image' || $handle == 'video') && rand(0, 2) > 0) {
                    $item['caption'] = fake()->sentences(rand(2, 4), true);
                }

                if (($handle == 'image' || $handle == 'video') && rand(0, 2) >= 0) {
                    $item['credits'] = 'Krænk Visuell';
                }

                if ($handle == 'image') {
                    $item['image_kind'] = 'random_url';
                }

                if ($handle == 'video') {
                    $video = $this->dummyEmbedCode();
                    $item['video_kind'] = 'embed';
                    $item['embed_code'] = $video['code'];
                    $item['aspect_width'] = $video['width'];
                    $item['aspect_height'] = $video['height'];
                }

                if ($handle == 'text') {
                    $item['text'] = $this->dummyText();
                }

                $content[] = $item;
            }
        }

        shuffle($content);

        return $content;
    }

    protected function dummyEmbedCode()
    {
        $codes = [
            [
                'width' => 16,
                'height' => 9,
                'code' => '<iframe title="vimeo-player" src="https://player.vimeo.com/video/276794240?h=f4a5fcab38" width="640" height="360" frameborder="0"    allowfullscreen></iframe>',
            ],
            [
                'width' => 16,
                'height' => 9,
                'code' => '<iframe src="https://player.vimeo.com/video/528761014?h=a9eeae6207" width="640" height="356" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
                    <p><a href="https://vimeo.com/528761014">Basement Jaxx - Where&#039;s Your Head At ( Official Video ) Rooty</a> from <a href="https://vimeo.com/user136338298">RoVa</a> on <a href="https://vimeo.com">Vimeo</a>.</p>',
            ],
            [
                'width' => 4,
                'height' => 3,
                'code' => '<iframe src="https://player.vimeo.com/video/6433175?h=d0edb14bbe" width="640" height="480" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
                <p><a href="https://vimeo.com/6433175">Queens Of The Stone Age - No One Knows</a> from <a href="https://vimeo.com/user2247357">Pecas</a> on <a href="https://vimeo.com">Vimeo</a>.</p>',
            ],
            [
                'width' => 16,
                'height' => 9,
                'code' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/dapqMeQCdcs" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>',
            ],
            [
                'width' => 16,
                'height' => 9,
                'code' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/XbByxzZ-4dI" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>',
            ],
            [
                'width' => 16,
                'height' => 9,
                'code' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/iTxOKsyZ0Lw" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>',
            ],
        ];

        return $codes[rand(0, count($codes) - 1)];
    }

    protected function dummyText()
    {
        $text = [];

        $elements = [
            'paragraph' => [
                'count' => rand(1, 4),
                'links' => true,
            ],
            // 'bulletList' => [
            //     'count' => rand(0, 1),
            //     'items' => rand(2, 5),
            // ],
        ];

        foreach ($elements as $handle => $params) {
            for ($n = 0; $n < $params['count']; $n++) {
                $item = [
                    'type' => $handle,
                    'content' => [],
                ];

                if ($handle == 'paragraph') {
                    if (! $params['links']) {
                        $item['content'] = [
                            [
                                'type' => 'text',
                                'text' => fake()->paragraph(rand(2, 5), true),
                            ],
                        ];
                    } else {
                        $item['content'] = [
                            [
                                'type' => 'text',
                                'text' => fake()->paragraph(rand(2, 5), true).' ',
                            ],
                            [
                                'type' => 'text',
                                'marks' => [
                                    [
                                        'type' => 'link',
                                        'attrs' => [
                                            'href' => 'https://kraenk.de',
                                        ],
                                    ],
                                ],
                                'text' => fake()->words(rand(1, 2), true),
                            ],
                            [
                                'type' => 'text',
                                'text' => ' '.fake()->paragraph(rand(2, 5), true),
                            ],
                        ];
                        // for ($n = 0; $n < $params['links']; $n++) {
                        //     $itemContent = [];
                        //     $itemContent[] = [
                        //         'type' => 'text',
                        //         'text' => fake()->paragraph(rand(2, 5), true).' ',
                        //     ];

                        //     $itemContent[] = [
                        //         'type' => 'text',
                        //         'marks' =>  [
                        //             'type' => 'link',
                        //             'attrs' => [
                        //                 'href' => '/test',
                        //             ]
                        //         ],
                        //         'text' => fake()->words(rand(1, 2), true),
                        //     ];

                        //     $itemContent[] = [
                        //         'type' => 'text',
                        //         'text' => ' '.fake()->paragraph(rand(2, 5), true),
                        //     ];

                        //     $item['content'] = $itemContent;
                        // }
                    }

                }

                if ($handle == 'bulletList') {
                    for ($n = 0; $n < $params['items']; $n++) {
                        $item['content'][] = [
                            'type' => 'listItem',
                            'content' => [[
                                'type' => 'paragraph',
                                'content' => [
                                    [
                                        'type' => 'text',
                                        'text' => fake()->paragraph(rand(1, 2), true),
                                    ],
                                ],
                            ]],
                        ];
                    }
                }

                $text[] = $item;
            }
        }

        shuffle($text);

        return $text;
    }
}
