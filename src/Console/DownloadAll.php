<?php

namespace Kraenkvisuell\StatamicHelpers\Console;

use Illuminate\Console\Command;

class DownloadAll extends Command
{
    public $signature = 'kv:download-all
        {--C|clear}';

    public function handle()
    {
        $clear = $this->option('clear');
        $message = $clear ? 'ACHTUNG! Alle bestehenden lokalen Dateien werden vorher gelöscht!'
                                      : 'ACHTUNG! Es werden Dateien zu den lokalen hinzugefügt!';

        if (! $this->confirm($message.' Wirklich weitermachen? [y|N]')) {
            return $this->info('Vorgang abgebrochen.');
        }

        $this->call('kv:download-assets', [
            '--clear' => $clear, '--force' => true,
        ]);

        $this->call('kv:download-collections', [
            '--clear' => $clear, '--force' => true,
        ]);

        $this->call('kv:download-globals', [
            '--clear' => $clear, '--force' => true,
        ]);

        $this->call('kv:download-trees', [
            '--clear' => $clear, '--force' => true,
        ]);

        $this->info('Download beendet');
    }
}
