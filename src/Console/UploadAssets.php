<?php

namespace Kraenkvisuell\StatamicHelpers\Console;

use Illuminate\Console\Command;

class UploadAssets extends Command
{
    public $signature = 'kv:upload-assets 
        {--P|production} 
        {--C|clear}';

    public function handle()
    {
        $env = $this->option('production') ? 'production' : 'staging';
        $mode = $this->option('clear') ? 'clear' : 'add';

        $message = $mode == 'clear' ? 'ACHTUNG! Alle bestehenden '.strtoupper($env).'-Medien werden vorher gelöscht!'
                                      : 'ACHTUNG! Es werden Medien zu '.strtoupper($env).' hinzugefügt!';

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

        $this->comment('Medien hochladen...');

        if ($mode == 'clear') {
            exec(
                'ssh '.$user.'@'.$host.' rm -rf /'.$sshPath.'/'.$assetsPath.'/*'
            );
        }

        exec(
            'scp -r '
            .$localPath.'/* '
            .$remoteString.'/'.$assetsPath
        );

        $this->info('Medien-Upload auf '.strtoupper($env).' beendet');
    }
}
