<?php

namespace Kraenkvisuell\StatamicHelpers\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UploadAssets extends Command
{
    public $signature = 'statamic-helpers:upload-assets {--P|production} {--R|replace}';

    public function handle()
    {
        $env = $this->option('production') ? 'production' : 'staging';
        $mode = $this->option('replace') ? 'replace' : 'add';

        $message = $mode == 'replace' ? 'ACHTUNG! Alle bestehenden '.strtoupper($env).'-Medien werden vorher gelöscht!'
                                      : 'ACHTUNG! Es werden Medien zu '.strtoupper($env).' hinzugefügt!';

        if (! $this->confirm($message.' Wirklich weitermachen? [y|N]')) {
            return $this->info('Vorgang abgebrochen.');
        }

        $user = config('statamic-helpers.remote.'.$env.'.ssh_user');
        $host = config('statamic-helpers.remote.'.$env.'.ssh_host');
        $path = config('statamic-helpers.remote.'.$env.'.ssh_path');

        $localPath = base_path(config('statamic-helpers.local.assets_path'));
        $remotePath = Str::beforeLast(config('statamic-helpers.remote.'.$env.'.assets_path'), '/');

        $remoteString = $user.'@'.$host.':'.$path;

        $this->comment('verbinden...');

        $this->comment('Medien hochladen...');

        // exec(
        //     'scp -r '
        //     .$localPath.' '
        //     .$remoteString.'/'.$remotePath
        // );

        $this->info('Medien-Upload auf '.strtoupper($env).' beendet');
    }
}
