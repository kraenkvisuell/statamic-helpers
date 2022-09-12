<?php

namespace Kraenkvisuell\StatamicTransferContent\Console;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

class AssetsToProduction extends Command
{
    public $signature = 'transfer-content:assets-to-production';

    public function handle()
    {
        if (!$this->confirm('ACHTUNG! Alle Live-Inhalte werden überschrieben! Wirklich weitermachen? [y|N]')) {
            return $this->info('Vorgang abgebrochen.');
        }

        $user = config('transfer-content.ssh_user');
        $host = config('transfer-content.ssh_host');
        $path = config('transfer-content.ssh_path');

        $localPath = base_path(config('transfer-content.local_assets_path'));
        $remotePath = Str::beforeLast(config('transfer-content.remote_assets_path'), '/');

        
        $remoteString = $user . '@' . $host . ':' . $path;

        $this->comment('connecting...');

        $this->comment('copying assets');

        exec(
            'scp -r '
            . $localPath . ' '
            . $remoteString .'/'. $remotePath
        );

        $this->info('done pushing to production.');
    }
}
