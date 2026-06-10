<?php

use App\Models\Berkas;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url(keep: true)] public string $filter = '';
    #[Url(keep: true)] public string $search = '';
    #[Url(keep: true)] public string $sort = 'created_at';
    #[Url(keep: true)] public string $direction = 'desc';

    public array $selected = [];

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilter() { $this->resetPage(); }

    public function setFilter(string $status): void
    {
        $this->filter = $this->filter === $status ? '' : $status;
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if ($this->sort === $column) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->direction = 'asc';
        }
    }

    public function deleteBerkas(int $id, AuditService $auditService): void
    {
        $berkas = Berkas::findOrFail($id);
        $this->authorize('delete', $berkas);
        $old = $berkas->toArray();
        $berkas->delete();
        $auditService->log('soft_deleted', $berkas, $old, null);
        session()->flash('success', 'Data berhasil dihapus.');
    }

    public function bulkDelete(AuditService $auditService): void
    {
        if (! Auth::user()->isAdmin()) abort(403);
        foreach ($this->selected as $id) {
            $berkas = Berkas::find($id);
            if ($berkas) {
                $old = $berkas->toArray();
                $berkas->delete();
                $auditService->log('soft_deleted', $berkas, $old, null);
            }
        }
        $this->selected = [];
        session()->flash('success', 'Berkas terpilih berhasil dihapus.');
    }

    public function requestDestruction(int $id, AuditService $auditService): void
    {
        $berkas = Berkas::findOrFail($id);
        $this->authorize('requestDestruction', $berkas);
        $berkas->update(['destruction_status' => 'pending']);
        $auditService->log('destruction_requested', $berkas, null, ['destruction_status' => 'pending']);
        session()->flash('success', 'Pengajuan pemusnahan berhasil dikirim.');
    }

    public function with(): array
    {
        $allowedSort = ['no_rm', 'nama_pasien', 'status', 'tgl_kunjungan_terakhir', 'tgl_retensi', 'created_at'];
        $sort = in_array($this->sort, $allowedSort) ? $this->sort : 'created_at';
        $direction = $this->direction === 'asc' ? 'asc' : 'desc';

        $query = Berkas::with('creator');
        if ($this->filter) $query->where('status', $this->filter);
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('no_rm', 'like', '%'.$this->search.'%')
                    ->orWhere('nama_pasien', 'like', '%'.$this->search.'%')
                    ->orWhere('nama_berkas', 'like', '%'.$this->search.'%')
                    ->orWhere('keterangan', 'like', '%'.$this->search.'%');
            });
        }

        return [
            'berkas' => $query->orderBy($sort, $direction)->paginate(10),
            'stats' => [
                'Aktif' => Berkas::where('status', 'Aktif')->count(),
                'Inaktif' => Berkas::where('status', 'Inaktif')->count(),
                'Musnah' => Berkas::where('status', 'Musnah')->count(),
            ],
        ];
    }
};
?>

<div class="p-8 flex-1 overflow-auto bg-slate-50">
    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-700 font-medium flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">{{ $filter ? 'Data '.$filter : 'Dashboard Utama' }}</h1>
            <p class="text-slate-500 mt-1 text-sm font-medium">Kelola status retensi berkas rekam medis Klinik Kolbu secara realtime.</p>
        </div>
        <div class="bg-white px-4 py-2.5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-2 text-slate-600 font-semibold text-sm">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>{{ now()->isoFormat('dddd, D MMMM YYYY') }}</span>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <button wire:click="setFilter('Aktif')" class="text-left focus:outline-none group relative overflow-hidden bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl p-6 text-white shadow-xl hover:shadow-emerald-500/25 hover:scale-[1.01] transition-all duration-300 {{ $filter === 'Aktif' ? 'ring-4 ring-emerald-300' : '' }}">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-xl group-hover:scale-110 transition-transform"></div>
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-emerald-100 text-xs font-bold uppercase tracking-wider">Berkas Aktif</p>
                    <p class="text-4xl font-extrabold mt-2">{{ $stats['Aktif'] }}</p>
                </div>
                <div class="w-14 h-14 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </button>

        <button wire:click="setFilter('Inaktif')" class="text-left focus:outline-none group relative overflow-hidden bg-gradient-to-br from-amber-500 to-orange-600 rounded-3xl p-6 text-white shadow-xl hover:shadow-amber-500/25 hover:scale-[1.01] transition-all duration-300 {{ $filter === 'Inaktif' ? 'ring-4 ring-amber-300' : '' }}">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-xl group-hover:scale-110 transition-transform"></div>
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-amber-100 text-xs font-bold uppercase tracking-wider">Berkas Inaktif</p>
                    <p class="text-4xl font-extrabold mt-2">{{ $stats['Inaktif'] }}</p>
                </div>
                <div class="w-14 h-14 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </button>

        <button wire:click="setFilter('Musnah')" class="text-left focus:outline-none group relative overflow-hidden bg-gradient-to-br from-rose-500 to-red-600 rounded-3xl p-6 text-white shadow-xl hover:shadow-rose-500/25 hover:scale-[1.01] transition-all duration-300 {{ $filter === 'Musnah' ? 'ring-4 ring-rose-300' : '' }}">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-xl group-hover:scale-110 transition-transform"></div>
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <p class="text-rose-100 text-xs font-bold uppercase tracking-wider">Berkas Dimusnahkan</p>
                    <p class="text-4xl font-extrabold mt-2">{{ $stats['Musnah'] }}</p>
                </div>
                <div class="w-14 h-14 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
            </div>
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/50">
            <div class="flex items-center gap-4 flex-1">
                <h2 class="text-xl font-bold text-slate-800">Data Rekam Medis</h2>
                @if ($filter)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-bold border border-blue-100 uppercase">
                        Filter: {{ $filter }}
                        <button wire:click="$set('filter', '')" class="hover:text-blue-800">&times;</button>
                    </span>
                @endif
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                <div class="relative w-full sm:w-72">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="search"
                           class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none text-sm font-medium"
                           placeholder="Cari No RM atau nama pasien...">
                </div>
                @if (count($selected) && auth()->user()->isAdmin())
                    <button wire:click="bulkDelete" wire:confirm="Hapus berkas terpilih?" class="px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-2xl text-sm font-bold shadow-sm">
                        Hapus ({{ count($selected) }})
                    </button>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100">
                        @if(auth()->user()->isAdmin())<th class="px-4 py-4 w-10"></th>@endif
                        @foreach (['no_rm' => 'No RM', 'nama_pasien' => 'Pasien', 'nama_berkas' => 'Berkas', 'status' => 'Status', 'tgl_kunjungan_terakhir' => 'Kunjungan', 'tgl_retensi' => 'Jatuh Tempo', 'created_by' => 'Petugas'] as $col => $label)
                            <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <button wire:click="sortBy('{{ $col }}')" class="hover:text-blue-600 transition-colors">{{ $label }}</button>
                            </th>
                        @endforeach
                        <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($berkas as $index => $row)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            @if(auth()->user()->isAdmin())
                            <td class="px-4 py-4"><input type="checkbox" wire:model.live="selected" value="{{ $row->id }}" class="rounded border-slate-300 text-blue-600"></td>
                            @endif
                            <td class="px-4 py-4 text-sm font-bold text-slate-800">{{ $row->no_rm }}</td>
                            <td class="px-4 py-4">
                                <div class="font-bold text-slate-800">{{ $row->nama_pasien }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $row->klasifikasi_label }}</div>
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-600">{{ $row->nama_berkas ?: '-' }}</td>
                            <td class="px-4 py-4">
                                @php
                                    $badge = match($row->status) {
                                        'Aktif' => 'bg-green-50 text-green-700 border-green-200',
                                        'Inaktif' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'Musnah' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        default => 'bg-slate-50 text-slate-600 border-slate-200',
                                    };
                                @endphp
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold border {{ $badge }}">{{ $row->status }}</span>
                                @if($row->legal_hold)<span class="text-xs text-red-500 block mt-1">Legal Hold</span>@endif
                                @if($row->destruction_status === 'pending')<span class="text-xs text-amber-600 block mt-1">Pending Musnah</span>@endif
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-600">{{ $row->tgl_kunjungan_terakhir?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-4 py-4 text-sm {{ $row->tgl_retensi && $row->tgl_retensi->isPast() ? 'text-red-600 font-bold' : 'text-slate-600' }}">
                                {{ $row->tgl_retensi?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-600">{{ $row->creator?->nama_lengkap ?? '-' }}</td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('berkas.show', $row) }}" class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-bold hover:bg-blue-100">Detail</a>
                                    <a href="{{ route('berkas.edit', $row) }}" class="px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-bold hover:bg-indigo-100">Edit</a>
                                    @if($row->file_pdf)
                                        <a href="{{ route('berkas.pdf', $row) }}" class="px-2.5 py-1 bg-red-50 text-red-700 rounded-lg text-xs font-bold hover:bg-red-100">PDF</a>
                                    @endif
                                    @if($row->status === 'Inaktif' && !$row->legal_hold && $row->destruction_status !== 'pending')
                                        <button wire:click="requestDestruction({{ $row->id }})" class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg text-xs font-bold hover:bg-amber-100">Musnah</button>
                                    @endif
                                    @can('delete', $row)
                                        <button wire:click="deleteBerkas({{ $row->id }})" wire:confirm="Hapus data ini?" class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold hover:bg-slate-200">Hapus</button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-16 text-center">
                                <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="font-bold text-slate-500">Belum Ada Data Rekam Medis</p>
                                <a href="{{ route('berkas.create') }}" class="inline-block mt-3 px-5 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg">Tambah Data</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($berkas->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">{{ $berkas->links() }}</div>
        @endif
    </div>
</div>
