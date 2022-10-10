<?php

namespace Kraenkvisuell\StatamicHelpers\Console;

use Illuminate\Console\Command;

class UploadAll extends Command
{
    public $signature = 'kv:upload-all
        {--C|clear}';

    public function handle()
    {
        $clear = $this->option('clear');
        $message = $clear ? 'ACHTUNG! Alle bestehenden Dateien werden vorher gelöscht!'
                          : 'ACHTUNG! Es werden Dateien hinzugefügt!';

        if (! $this->confirm($message.' Wirklich weitermachen? [y|N]')) {
            return $this->info('Vorgang abgebrochen.');
        }

        $this->call('kv:upload-assets', [
            '--clear' => $clear, '--force' => true,
        ]);

        $this->call('kv:upload-collections', [
            '--clear' => $clear, '--force' => true,
        ]);

        $this->call('kv:upload-globals', [
            '--clear' => $clear, '--force' => true,
        ]);

        $this->call('kv:upload-trees', [
            '--clear' => $clear, '--force' => true,
        ]);

        $this->info('Upload beendet');
    }
}
