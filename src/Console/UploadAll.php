<?php

namespace Kraenkvisuell\StatamicHelpers\Console;

use Illuminate\Console\Command;

class UploadAll extends Command
{
    public $signature = 'kv:upload-all
        {--P|production} 
        {--C|clear}';

    public function handle()
    {
        $clear = $this->option('clear');
        $production = $this->option('production');

        $message = $clear ? 'ACHTUNG! Alle bestehenden Dateien werden vorher gelöscht!'
                          : 'ACHTUNG! Es werden Dateien hinzugefügt!';

        if (! $this->confirm($message.' Wirklich weitermachen? [y|N]')) {
            return $this->info('Vorgang abgebrochen.');
        }

        $this->call('kv:upload-assets', [
            '--clear' => $clear, '--production' => $production, '--force' => true,
        ]);

        $this->call('kv:upload-collections', [
            '--clear' => $clear, '--production' => $production, '--force' => true,
        ]);

        $this->call('kv:upload-globals', [
            '--clear' => $clear, '--production' => $production, '--force' => true,
        ]);

        $this->call('kv:upload-trees', [
            '--clear' => $clear, '--production' => $production, '--force' => true,
        ]);

        $this->info('Upload beendet');
    }
}
