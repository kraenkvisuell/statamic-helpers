<?php

namespace Kraenkvisuell\StatamicHelpers\Console;

use Illuminate\Console\Command;

class DownloadCollections extends Command
{
    public $signature = 'statamic-helpers:download-collections 
        {collections*} 
        {--P|production} 
        {--C|clear}';

    public function handle()
    {
        $env = $this->option('production') ? 'production' : 'staging';
        $mode = $this->option('clear') ? 'clear' : 'add';
        $collections = $this->argument('collections');

        $message = $mode == 'clear' ? 'ACHTUNG! Alle bestehenden lokalen Dateien werden vorher gelöscht!'
                                      : 'ACHTUNG! Es werden Dateien zu den lokalen hinzugefügt!';

        if (! $this->confirm($message.' Wirklich weitermachen? [y|N]')) {
            return $this->info('Vorgang abgebrochen.');
        }

        $user = config('statamic-helpers.remote.'.$env.'.ssh_user');
        $host = config('statamic-helpers.remote.'.$env.'.ssh_host');
        $sshPath = config('statamic-helpers.remote.'.$env.'.ssh_path');
        $collectionsPath = 'content/collections';
        $localPath = base_path($collectionsPath);

        $remoteString = $user.'@'.$host.':'.'/'.$sshPath;

        $this->comment('verbinden...');
        $this->comment('Dateien downloaden...');

        foreach ($collections as $collection) {
            $this->comment($collection.' downloaden...');

            if ($mode == 'clear') {
                exec(
                    'rm -rf '.$localPath.'/'.$collection
                );
            }

            exec(
                'scp -r '
                .$remoteString.'/'.$collectionsPath.'/'.$collection.' '
                .$localPath
            );
        }

        $this->info('Sammlungen-Download von '.strtoupper($env).' beendet');
    }
}
