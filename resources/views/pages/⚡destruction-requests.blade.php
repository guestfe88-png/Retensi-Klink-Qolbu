<?php

use App\Models\Berkas;
use App\Models\DestructionCertificate;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public function approve(int $id, AuditService $auditService): void
    {
        $berkas = Berkas::findOrFail($id);
        $this->authorize('approveDestruction', $berkas);

        $berkas->update([
            'status' => 'Musnah',
            'destruction_status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $certificate = DestructionCertificate::create([
            'berkas_id' => $berkas->id,
            'certificate_number' => 'DM-'.now()->format('Ymd').'-'.str_pad((string) $berkas->id, 5, '0', STR_PAD_LEFT),
            'approved_by' => Auth::id(),
            'destroyed_at' => now(),
            'catatan' => 'Pemusnahan disetujui melalui sistem retensi.',
        ]);

        if ($berkas->file_pdf) {
            Storage::disk('local')->delete('berkas/'.$berkas->file_pdf);
            Storage::disk('public')->delete('berkas/'.$berkas->file_pdf);
            $berkas->update(['file_pdf' => null]);
        }

        $auditService->log('destruction_approved', $berkas, null, ['certificate' => $certificate->certificate_number]);
        session()->flash('success', 'Pemusnahan disetujui. Sertifikat: '.$certificate->certificate_number);
    }

    public function reject(int $id, AuditService $auditService): void
    {
        $berkas = Berkas::findOrFail($id);
        $this->authorize('approveDestruction', $berkas);

        $berkas->update(['destruction_status' => 'rejected']);
        $auditService->log('destruction_rejected', $berkas, null, ['destruction_status' => 'rejected']);
        session()->flash('success', 'Pengajuan pemusnahan ditolak.');
    }

    public function with(): array
    {
        return [
            'requests' => Berkas::with('creator')
                ->where('destruction_status', 'pending')
                ->latest()
                ->paginate(10),
        ];
    }
};
?>

<div class="p-6 flex-1 overflow-auto">
    <h1 class="text-2xl font-bold mb-6">Persetujuan Pemusnahan</h1>
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-emerald-50 rounded-2xl text-emerald-700">{{ session('success') }}</div>
    @endif
    <div class="bg-white rounded-2xl border divide-y">
        @forelse ($requests as $req)
            <div class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div>
                    <p class="font-bold">{{ $req->nama_pasien }} ({{ $req->no_rm }})</p>
                    <p class="text-sm text-slate-500">Rawat Jalan — diajukan oleh {{ $req->creator?->nama_lengkap }}</p>
                </div>
                <div class="flex gap-2">
                    <button wire:click="approve({{ $req->id }})" wire:confirm="Setujui pemusnahan?" class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-bold">Setujui</button>
                    <button wire:click="reject({{ $req->id }})" class="px-4 py-2 bg-slate-200 rounded-xl text-sm font-bold">Tolak</button>
                    <a href="{{ route('berkas.show', $req) }}" class="px-4 py-2 bg-blue-50 text-blue-700 rounded-xl text-sm font-bold">Detail</a>
                </div>
            </div>
        @empty
            <p class="p-8 text-center text-slate-400">Tidak ada pengajuan pending.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $requests->links() }}</div>
</div>
