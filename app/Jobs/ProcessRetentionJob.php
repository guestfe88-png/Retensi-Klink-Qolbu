<?php

namespace App\Jobs;

use App\Services\RetentionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessRetentionJob implements ShouldQueue
{
    use Queueable;

    public function handle(RetentionService $retentionService): void
    {
        $retentionService->processAll();
    }
}
