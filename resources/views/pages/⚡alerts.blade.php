<?php

use App\Services\RetentionService;
use Livewire\Component;

new class extends Component
{
    public function with(RetentionService $retentionService): array
    {
        return ['alerts' => $retentionService->alertQuery()];
    }
};
?>

<div class="p-6 flex-1 overflow-auto">
    <h1 class="text-2xl font-bold mb-6">Peringatan Retensi</h1>
    <div class="bg-white rounded-2xl border divide-y">
        @forelse ($alerts as $alert)
            <div class="p-4 flex justify-between items-center">
                <div>
                    <p class="font-bold">{{ $alert->nama_pasien }} — RM {{ $alert->no_rm }}</p>
                    <p class="text-sm text-slate-500">Status: {{ $alert->status }} | Jatuh tempo: {{ $alert->tgl_retensi?->format('d/m/Y') }}</p>
                </div>
                <a href="{{ route('berkas.show', $alert) }}" class="text-blue-600 font-bold text-sm">Detail</a>
            </div>
        @empty
            <p class="p-8 text-center text-slate-400">Tidak ada peringatan.</p>
        @endforelse
    </div>
</div>
