<?php

namespace Kraenkvisuell\StatamicHelpers\Console;

use Illuminate\Console\Command;

class UploadTrees extends Command
{
    public $signature = 'kv:upload-trees 
        {--P|production} 
        {--C|clear} 
        {--F|force}';

    public function handle()
    {
        $env = $this->option('production') ? 'production' : 'staging';
        $mode = $this->option('clear') ? 'clear' : 'add';

        $message = $mode == 'clear' ? 'ACHTUNG! Alle bestehenden '.strtoupper($env).'-Dateien werden vorher gelöscht!'
                                      : 'ACHTUNG! Es werden Dateien zu '.strtoupper($env).' hinzugefügt!';

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
        $this->comment('Dateien uploaden...');

        foreach (['collections', 'navigation'] as $subPath) {
            $this->comment($subPath.' uploaden...');

            if ($mode == 'clear') {
                exec(
                    'ssh '.$user.'@'.$host.' rm -rf /'.$sshPath.'/'.$treesPath.'/'.$subPath
                );
            }

            exec(
                'scp -r '
                .$localPath.'/'.$subPath.' '
                .$remoteString.'/'.$treesPath
            );
        }

        $this->info('Trees-Upload von '.strtoupper($env).' beendet');
    }
}
