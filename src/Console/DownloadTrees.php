<?php

namespace Kraenkvisuell\StatamicHelpers\Console;

use Illuminate\Console\Command;

class DownloadTrees extends Command
{
    public $signature = 'kv:download-trees 
        {--P|production} 
        {--C|clear}
        {--F|force}';

    public function handle()
    {
        $env = $this->option('production') ? 'production' : 'staging';
        $mode = $this->option('clear') ? 'clear' : 'add';

        $message = $mode == 'clear' ? 'ACHTUNG! Alle bestehenden lokalen Dateien werden vorher gelöscht!'
                                      : 'ACHTUNG! Es werden Dateien zu den lokalen hinzugefügt!';

        if (! $this->option('force') && ! $this->confirm($message.' Wirklich weitermachen? [y|N]')) {
            return $this->info('Vorgang abgebrochen.');
        }

        $user = config('statamic-helpers.remote.'.$env.'.ssh_user');
        $host = config('statamic-helpers.remote.'.$env.'.ssh_host');
        $sshPath = config('statamic-helpers.remote.'.$env.'.ssh_path');
        $treesPath = 'content/trees';
        $localPath = base_path($treesPath);

        $remoteString = $user.'@'.$host.':'.'/'.$sshPath;

        $this->comment('verbinden...');
        $this->comment('Dateien downloaden...');

        foreach (['collections', 'navigation'] as $subPath) {
            $this->comment($subPath.' downloaden...');

            if ($mode == 'clear') {
                exec(
                    'rm -rf '.$localPath.'/'.$subPath
                );
            }

            exec(
                'scp -r '
                .$remoteString.'/'.$treesPath.'/'.$subPath.' '
                .$localPath
            );
        }

        $this->info('Trees-Download von '.strtoupper($env).' beendet');
    }
}
