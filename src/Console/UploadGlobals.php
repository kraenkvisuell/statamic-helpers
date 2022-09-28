<?php

namespace Kraenkvisuell\StatamicHelpers\Console;

use Illuminate\Console\Command;

class UploadGlobals extends Command
{
    public $signature = 'kv:upload-globals 
        {--P|production} 
        {--C|clear}';

    public function handle()
    {
        $env = $this->option('production') ? 'production' : 'staging';
        $mode = $this->option('clear') ? 'clear' : 'add';

        $message = $mode == 'clear' ? 'ACHTUNG! Alle bestehenden '.strtoupper($env).'-Dateien werden vorher gelöscht!'
                                      : 'ACHTUNG! Es werden Dateien zu '.strtoupper($env).' hinzugefügt!';

        if (! $this->confirm($message.' Wirklich weitermachen? [y|N]')) {
            return $this->info('Vorgang abgebrochen.');
        }

        $user = config('statamic-helpers.remote.'.$env.'.ssh_user');
        $host = config('statamic-helpers.remote.'.$env.'.ssh_host');
        $sshPath = config('statamic-helpers.remote.'.$env.'.ssh_path');
        $globalsPath = 'content/globals';
        $localPath = base_path($globalsPath);

        $remoteString = $user.'@'.$host.':'.'/'.$sshPath;

        $this->comment('verbinden...');
        $this->comment('Dateien uploaden...');

        if ($mode == 'clear') {
            exec(
                'ssh '.$user.'@'.$host.' rm -rf /'.$sshPath.'/'.$globalsPath.'/*.yaml'
            );
        }

        exec(
            'scp -r '
            .$localPath.'/*.yaml '
            .$remoteString.'/'.$globalsPath
        );

        $this->info('Globals-Upload von '.strtoupper($env).' beendet');
    }
}
