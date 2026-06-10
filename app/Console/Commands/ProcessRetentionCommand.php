<?php

namespace App\Console\Commands;

use App\Services\RetentionService;
use Illuminate\Console\Command;

class ProcessRetentionCommand extends Command
{
    protected $signature = 'retensi:process';

    protected $description = 'Proses retensi otomatis: ubah status dan ajukan pemusnahan';

    public function handle(RetentionService $retentionService): int
    {
        $stats = $retentionService->processAll();

        $this->info("Selesai. Inaktif: {$stats['inaktif']}, Pending musnah: {$stats['pending_destruction']}");

        return self::SUCCESS;
    }
}
