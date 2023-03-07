<?php

namespace Kraenkvisuell\StatamicHelpers\Console;

use Illuminate\Console\Command;

class DownloadGlobals extends Command
{
    public $signature = 'kv:download-globals 
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
        $globalsPath = 'content/globals';
        $localPath = base_path('content');

        $remoteString = $user.'@'.$host.':'.'/'.$sshPath;

        $this->comment('verbinden...');
        $this->comment('Dateien downloaden...');

        if ($mode == 'clear') {
            exec(
                'rm -rf '.$localPath.'/globals'
            );
        }

        exec(
            'scp -r '
            .$remoteString.'/'.$globalsPath.' '
            .$localPath
        );

        $this->info('Globals-Download von '.strtoupper($env).' beendet');
    }
}
