<?php

use App\Models\Berkas;
use Livewire\Component;

new class extends Component
{
    public Berkas $berkas;

    public function mount($id)
    {
        $this->berkas = Berkas::with(['creator', 'patient', 'approver', 'destructionCertificate'])->findOrFail($id);
        $this->authorize('view', $this->berkas);
    }
};
?>

<div class="p-6 flex-1 overflow-auto">
    <div class="max-w-4xl mx-auto bg-white rounded-3xl border shadow-sm overflow-hidden">
        <div class="p-6 border-b flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Detail Rekam Medis</h1>
                <p class="text-slate-500 text-sm">RM: {{ $berkas->no_rm }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('berkas.edit', $berkas) }}" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold">Edit</a>
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-slate-100 rounded-xl text-sm font-bold">Kembali</a>
            </div>
        </div>

        <div class="p-6 grid md:grid-cols-2 gap-4 text-sm">
            <div><span class="text-slate-500">Nama Pasien</span><p class="font-bold">{{ $berkas->nama_pasien }}</p></div>
            <div><span class="text-slate-500">Klasifikasi</span><p class="font-bold">Rawat Jalan</p></div>
            <div><span class="text-slate-500">Status</span><p class="font-bold">{{ $berkas->status }}</p></div>
            <div><span class="text-slate-500">Terakhir Kunjungan</span><p class="font-bold">{{ $berkas->tgl_kunjungan_terakhir?->format('d/m/Y') ?? '-' }}</p></div>
            <div><span class="text-slate-500">Jatuh Tempo Retensi</span><p class="font-bold {{ $berkas->tgl_retensi?->isPast() ? 'text-red-600' : '' }}">{{ $berkas->tgl_retensi?->format('d/m/Y') ?? '-' }}</p></div>
            <div><span class="text-slate-500">Legal Hold</span><p class="font-bold">{{ $berkas->legal_hold ? 'Ya' : 'Tidak' }}</p></div>
            <div><span class="text-slate-500">Alamat</span><p class="font-bold">{{ $berkas->alamat ?: '-' }}</p></div>
            <div><span class="text-slate-500">Petugas</span><p class="font-bold">{{ $berkas->creator?->nama_lengkap ?? '-' }}</p></div>
            <div class="md:col-span-2"><span class="text-slate-500">Keterangan</span><p class="font-bold">{{ $berkas->keterangan ?: '-' }}</p></div>
        </div>

        <div class="p-6 border-t flex gap-3">
            @if($berkas->file_pdf)
                <a href="{{ route('berkas.pdf', $berkas) }}" class="px-4 py-2 bg-red-50 text-red-700 rounded-xl font-bold text-sm">Unduh PDF</a>
            @endif
            @if($berkas->destructionCertificate)
                <a href="{{ route('certificates.show', $berkas->destructionCertificate) }}" target="_blank" class="px-4 py-2 bg-slate-100 rounded-xl font-bold text-sm">Sertifikat Pemusnahan</a>
            @endif
        </div>
    </div>
</div>
