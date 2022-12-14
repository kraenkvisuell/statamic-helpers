<?php

namespace Kraenkvisuell\StatamicHelpers\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class UploadCollections extends Command
{
    public $signature = 'kv:upload-collections 
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

        $message = $mode == 'clear' ? 'ACHTUNG! Alle bestehenden '.strtoupper($env).'-Dateien werden vorher gelöscht!'
                                      : 'ACHTUNG! Es werden Dateien zu '.strtoupper($env).' hinzugefügt!';

        if (! $this->option('force') && ! $this->confirm($message.' Wirklich weitermachen? [y|N]')) {
            return $this->info('Vorgang abgebrochen.');
        }

        $user = config('statamic-helpers.remote.'.$env.'.ssh_user');
        $host = config('statamic-helpers.remote.'.$env.'.ssh_host');
        $sshPath = config('statamic-helpers.remote.'.$env.'.ssh_path');
        $remoteString = $user.'@'.$host.':'.'/'.$sshPath;

        $this->comment('verbinden...');
        $this->comment('Dateien uploaden...');

        foreach ($collections as $collection) {
            $this->comment($collection.' uploaden...');

            if ($mode == 'clear') {
                ray('ssh '.$user.'@'.$host.' rm -rf /'.$sshPath.'/'.$collectionsPath.'/'.$collection.'/*');
                exec(
                    'ssh '.$user.'@'.$host.' rm -rf /'.$sshPath.'/'.$collectionsPath.'/'.$collection.'/*'
                );
            }

            exec(
                'scp -r '
                .$localPath.'/'.$collection.' '
                .$remoteString.'/'.$collectionsPath

            );
        }

        $this->info('Sammlungen-Upload von '.strtoupper($env).' beendet');
    }
}
