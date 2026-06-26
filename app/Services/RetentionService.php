<?php

namespace App\Services;

use App\Models\Berkas;
use App\Models\RetentionPolicy;
use Carbon\Carbon;

class RetentionService
{
    public function __construct(private AuditService $auditService) {}

    public function recalculate(Berkas $berkas): Berkas
    {
        $policy = RetentionPolicy::forKlasifikasi($berkas->klasifikasi);
        $baseDate = $berkas->tgl_kunjungan_terakhir
            ? Carbon::parse($berkas->tgl_kunjungan_terakhir)
            : ($berkas->created_at ?? now());

        if ($berkas->status === 'Aktif') {
            $berkas->tgl_retensi = $baseDate->copy()->addYears($policy->tahun_aktif);
        } elseif ($berkas->status === 'Inaktif') {
            $berkas->tgl_retensi = $baseDate->copy()->addYears($policy->tahun_aktif + $policy->tahun_inaktif);
        } else {
            $berkas->tgl_retensi = null;
        }

        return $berkas;
    }

    public function processAll(): array
    {
        $stats = ['inaktif' => 0, 'pending_destruction' => 0];

        Berkas::query()
            ->where('legal_hold', false)
            ->whereIn('status', ['Aktif', 'Inaktif'])
            ->chunkById(100, function ($records) use (&$stats) {
                foreach ($records as $berkas) {
                    $this->recalculate($berkas);

                    if ($berkas->status === 'Aktif' && $berkas->tgl_retensi && $berkas->tgl_retensi->isPast()) {
                        $old = $berkas->only(['status', 'tgl_retensi']);
                        $berkas->status = 'Inaktif';
                        $this->recalculate($berkas);
                        $berkas->save();
                        $this->auditService->log('retention_auto_inaktif', $berkas, $old, $berkas->only(['status', 'tgl_retensi']));
                        $stats['inaktif']++;
                        continue;
                    }

                    if (
                        $berkas->status === 'Inaktif'
                        && $berkas->tgl_retensi
                        && $berkas->tgl_retensi->isPast()
                        && $berkas->destruction_status !== 'pending'
                    ) {
                        $old = $berkas->only(['destruction_status']);
                        $berkas->destruction_status = 'pending';
                        $berkas->save();
                        $this->auditService->log('retention_pending_musnah', $berkas, $old, $berkas->only(['destruction_status']));
                        $stats['pending_destruction']++;
                    }
                }
            });

        return $stats;
    }

    public function alertQuery()
    {
        $policy = RetentionPolicy::forKlasifikasi();
        $alertDays = $policy->alert_hari;

        return Berkas::query()
            ->whereIn('status', ['Aktif', 'Inaktif'])
            ->where('legal_hold', false)
            ->whereNotNull('tgl_retensi')
            ->get()
            ->filter(fn (Berkas $berkas) => $berkas->tgl_retensi->lte(now()->addDays($alertDays)));
    }
}
