<?php

namespace Kraenkvisuell\StatamicHelpers\Console;


use Statamic\Facades\Nav;
use Illuminate\Support\Str;
use Statamic\Facades\Entry;
use Illuminate\Console\Command;
use Facades\Statamic\Structures\BranchIdGenerator;

class GenerateDummySite extends Command
{
    public $signature = 'kv:dummy-site';

    private $data;
    private $generatedIds = [];

    public function handle()
    {
        $message = 'ACHTUNG! Alle bestehenden Seiten, Posts und sonstige Einträge werden vorher gelöscht!';

        if (! $this->confirm($message.' Wirklich weitermachen? [y|N]')) {
            return $this->info('Vorgang abgebrochen.');
        }

        $pageCount = 30;

        Entry::query()->where('collection', 'pages')->get()->each->delete();
        Entry::query()->where('collection', 'posts')->get()->each->delete();

        $pagesForNav = [];

        for($pageIndex = 0; $pageIndex < $pageCount; $pageIndex++) {
            if ($pageIndex == 0) {
                $title = 'Home';
            } else {
                $title = ucfirst(fake()->words(rand(1, 3), true));
            }
            $slug = Str::slug($title);

            $entry = Entry::make()->collection('pages')->slug($slug);
            $entry->set('title', $title);
            $entry->set('main_replicator', [
                [
                    'id' => Str::random(8),
                    'headline' => ucfirst(fake()->words(rand(1, 3), true)),
                    'headline_level' => 'h2',
                    'kind' => 'random_url',
                    'type' => 'image',
                    'enabled' => true,
                ],
            ]);

            $entry->save();

            $pagesForNav[] = [
                'id' => Str::uuid()->toString(),
                'entry' => $entry->id(),
            ];
        }

        
        Nav::findByHandle('main_nav')?->delete();
        
        $nav = Nav::make()
            ->title('Hauptmenü')
            ->handle('main_menu')
            ->collections(['pages']);

        $nav->makeTree('default')->save();

        // $nav->blueprint()
        //     ->ensureField('title', ['type' => 'text'])
        //     ->ensureField('url', ['type' => 'text']);

        $nav->in('default')->tree($pagesForNav)->save();
        
        
        $this->call('cache:clear');

        $this->info('Dummy-Site generiert');
    }
}
