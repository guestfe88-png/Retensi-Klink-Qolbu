<?php

use App\Models\RetentionPolicy;
use Livewire\Component;

new class extends Component
{
    public array $policies = [];

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->loadPolicies();
    }

    public function loadPolicies(): void
    {
        $this->policies = RetentionPolicy::query()
            ->rawatJalan()
            ->orderBy('id')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'nama' => $p->nama,
                'klasifikasi' => $p->klasifikasi,
                'tahun_aktif' => $p->tahun_aktif,
                'tahun_inaktif' => $p->tahun_inaktif,
                'alert_hari' => $p->alert_hari,
                'keterangan' => $p->keterangan,
                'is_active' => $p->is_active,
            ])->toArray();
    }

    public function save(): void
    {
        foreach ($this->policies as $policy) {
            RetentionPolicy::query()
                ->rawatJalan()
                ->where('id', $policy['id'])
                ->update([
                'tahun_aktif' => (int) $policy['tahun_aktif'],
                'tahun_inaktif' => (int) $policy['tahun_inaktif'],
                'alert_hari' => (int) $policy['alert_hari'],
                'keterangan' => $policy['keterangan'],
                'is_active' => (bool) $policy['is_active'],
            ]);
        }

        session()->flash('success', 'Aturan retensi berhasil diperbarui.');
    }
};
?>

<div class="p-6 flex-1 overflow-auto">
    <h1 class="text-2xl font-bold mb-2">Aturan Retensi — Rawat Jalan</h1>
    <p class="text-slate-500 text-sm mb-6">Kebijakan retensi khusus unit <strong>Rawat Jalan</strong>. Status berubah otomatis via scheduler harian.</p>

    @if (empty($policies))
        <div class="mb-4 p-4 bg-amber-50 rounded-2xl text-amber-800 text-sm">
            Aturan retensi Rawat Jalan belum tersedia. Jalankan <code class="font-mono">php artisan migrate</code>.
        </div>
    @endif

    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-emerald-50 rounded-2xl text-emerald-700">{{ session('success') }}</div>
    @endif

    <form wire:submit="save" class="space-y-4">
        @foreach ($policies as $index => $policy)
            <div class="bg-white border rounded-2xl p-5">
                <h2 class="font-bold mb-1">{{ $policy['nama'] }}</h2>
                <p class="text-xs text-slate-500 mb-3 uppercase tracking-wide font-semibold">Unit: Rawat Jalan</p>
                <div class="grid md:grid-cols-4 gap-3">
                    <div>
                        <label class="text-xs font-bold text-slate-500">Tahun Aktif</label>
                        <input type="number" wire:model="policies.{{ $index }}.tahun_aktif" class="w-full px-3 py-2 border rounded-xl" min="1">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Tahun Inaktif</label>
                        <input type="number" wire:model="policies.{{ $index }}.tahun_inaktif" class="w-full px-3 py-2 border rounded-xl" min="1">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-slate-500">Alert (hari)</label>
                        <input type="number" wire:model="policies.{{ $index }}.alert_hari" class="w-full px-3 py-2 border rounded-xl" min="1">
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="policies.{{ $index }}.is_active">
                            <span class="text-sm font-semibold">Aktif</span>
                        </label>
                    </div>
                </div>
                <textarea wire:model="policies.{{ $index }}.keterangan" rows="2" class="w-full mt-3 px-3 py-2 border rounded-xl text-sm"></textarea>
            </div>
        @endforeach
        <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-bold">Simpan Aturan</button>
    </form>
</div>
