<?php

namespace Kraenkvisuell\StatamicHelpers\Console;

use Illuminate\Console\Command;

class DownloadAssets extends Command
{
    public $signature = 'kv:download-assets {--P|production} {--C|clear}';

    public function handle()
    {
        $env = $this->option('production') ? 'production' : 'staging';
        $mode = $this->option('clear') ? 'clear' : 'add';

        $message = $mode == 'clear' ? 'ACHTUNG! Alle bestehenden lokalen Medien werden vorher gelöscht!'
                                      : 'ACHTUNG! Es werden Medien zu den lokalen hinzugefügt!';

        if (! $this->confirm($message.' Wirklich weitermachen? [y|N]')) {
            return $this->info('Vorgang abgebrochen.');
        }

        $user = config('statamic-helpers.remote.'.$env.'.ssh_user');
        $host = config('statamic-helpers.remote.'.$env.'.ssh_host');
        $sshPath = config('statamic-helpers.remote.'.$env.'.ssh_path');
        $assetsPath = config('statamic-helpers.remote.'.$env.'.assets_path');

        $localPath = base_path(config('statamic-helpers.local.assets_path'));
        if (! $localPath) {
            return;
        }

        $remoteString = $user.'@'.$host.':'.'/'.$sshPath;

        $this->comment('verbinden...');
        $this->comment('Medien downloaden...');

        if ($mode == 'clear') {
            exec(
                'rm -rf '.$localPath.'/*'
            );
        }

        exec(
            'scp -r '
            .$remoteString.'/'.$assetsPath.'/* '
            .$localPath
        );

        $this->info('Medien-Download von '.strtoupper($env).' beendet');
    }
}
