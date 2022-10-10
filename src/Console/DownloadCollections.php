<?php

namespace Kraenkvisuell\StatamicHelpers\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DownloadCollections extends Command
{
    public $signature = 'kv:download-collections 
        {collections?*} 
        {--P|production} 
        {--C|clear} 
        {--F|force}';

    public function handle()
    {
        $env = $this->option('production') ? 'production' : 'staging';
        $mode = $this->option('clear') ? 'clear' : 'add';
        $collections = $this->argument('collections');

        $collectionsPath = 'content/collections';
        $localPath = base_path($collectionsPath);

        if (! count($collections)) {
            foreach (File::files($localPath) as $file) {
                if ($file->getExtension() == 'yaml') {
                    ray($file);
                    $collections[] = Str::before($file->getFilename(), '.');
                }
            }
        }

        $message = $mode == 'clear' ? 'ACHTUNG! Alle bestehenden lokalen Dateien werden vorher gelöscht!'
                                      : 'ACHTUNG! Es werden Dateien zu den lokalen hinzugefügt!';

        if (! $this->option('force') && ! $this->confirm($message.' Wirklich weitermachen? [y|N]')) {
            return $this->info('Vorgang abgebrochen.');
        }

        $user = config('statamic-helpers.remote.'.$env.'.ssh_user');
        $host = config('statamic-helpers.remote.'.$env.'.ssh_host');
        $sshPath = config('statamic-helpers.remote.'.$env.'.ssh_path');

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
