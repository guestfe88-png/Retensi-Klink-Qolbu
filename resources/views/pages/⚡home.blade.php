<?php

use App\Models\Berkas;
use App\Services\RetentionService;
use Livewire\Component;

new class extends Component
{
    public function with(RetentionService $retentionService): array
    {
        $aktif = Berkas::where('status', 'Aktif')->count();
        $inaktif = Berkas::where('status', 'Inaktif')->count();
        $musnah = Berkas::where('status', 'Musnah')->count();
        $pending = Berkas::where('destruction_status', 'pending')->count();

        return [
            'stats' => [
                'Aktif' => $aktif,
                'Inaktif' => $inaktif,
                'Musnah' => $musnah,
                'Pending' => $pending,
                'Total' => $aktif + $inaktif + $musnah,
            ],
            'alerts' => $retentionService->alertQuery()->take(5),
        ];
    }
};
?>

<div class="p-8 flex-1 overflow-auto bg-slate-50">
    <div class="max-w-7xl mx-auto space-y-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ config('app.name') }}</h1>
                <p class="text-slate-500 mt-1.5 text-sm font-medium">Pantau statistik ringkasan retensi rekam medis rawat jalan Klinik Kolbu.</p>
            </div>
            <div class="bg-white px-4 py-3 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-2 text-slate-600 font-semibold text-sm">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>{{ now()->isoFormat('dddd, D MMMM YYYY') }}</span>
            </div>
        </div>

        {{-- Welcome Banner --}}
        <div class="relative overflow-hidden bg-slate-900 rounded-[2rem] p-8 md:p-10 text-white shadow-xl border border-slate-800">
            <div class="absolute -right-10 -top-10 w-48 h-48 bg-blue-600/20 rounded-full blur-3xl"></div>
            <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-3 max-w-2xl">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-500/10 text-blue-400 rounded-full text-xs font-bold border border-blue-500/20 uppercase tracking-wider">
                        Selamat Datang Kembali
                    </span>
                    <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight">{{ Auth::user()->nama_lengkap }}</h2>
                    <p class="text-slate-400 text-sm md:text-base leading-relaxed">
                        Anda masuk sebagai <strong class="text-white">{{ Auth::user()->role }}</strong> di <strong class="text-white">{{ config('app.name') }}</strong>.
                    </p>
                </div>
                <div class="flex-shrink-0 bg-white/5 border border-white/10 backdrop-blur-md rounded-2xl p-6 flex flex-col items-center justify-center min-w-[180px] text-center">
                    <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Berkas</span>
                    <span class="text-4xl font-extrabold mt-1 text-white">{{ $stats['Total'] }}</span>
                    <span class="text-slate-500 text-[10px] mt-1.5 font-medium">Berkas Terarsip</span>
                </div>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="{{ route('dashboard', ['filter' => 'Aktif']) }}" class="group relative overflow-hidden bg-gradient-to-br from-emerald-500 to-teal-600 rounded-[2rem] p-8 text-white shadow-lg shadow-emerald-500/10 hover:shadow-emerald-500/25 hover:scale-[1.02] transition-all duration-300 min-h-[200px] flex flex-col justify-between">
                <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-white/10 rounded-full blur-2xl group-hover:scale-125 transition-transform"></div>
                <div class="flex items-start justify-between relative z-10">
                    <div>
                        <p class="text-emerald-100 text-xs font-bold uppercase tracking-wider">Berkas Aktif</p>
                        <p class="text-5xl font-black mt-2">{{ $stats['Aktif'] }}</p>
                    </div>
                    <div class="w-14 h-14 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="relative z-10 pt-4 flex items-center gap-1.5 text-emerald-100 font-bold text-xs uppercase group-hover:translate-x-1 transition-transform">
                    <span>Lihat Rincian</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            <a href="{{ route('dashboard', ['filter' => 'Inaktif']) }}" class="group relative overflow-hidden bg-gradient-to-br from-amber-500 to-orange-600 rounded-[2rem] p-8 text-white shadow-lg shadow-amber-500/10 hover:shadow-amber-500/25 hover:scale-[1.02] transition-all duration-300 min-h-[200px] flex flex-col justify-between">
                <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-white/10 rounded-full blur-2xl group-hover:scale-125 transition-transform"></div>
                <div class="flex items-start justify-between relative z-10">
                    <div>
                        <p class="text-amber-100 text-xs font-bold uppercase tracking-wider">Berkas Inaktif</p>
                        <p class="text-5xl font-black mt-2">{{ $stats['Inaktif'] }}</p>
                    </div>
                    <div class="w-14 h-14 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="relative z-10 pt-4 flex items-center gap-1.5 text-amber-100 font-bold text-xs uppercase group-hover:translate-x-1 transition-transform">
                    <span>Lihat Rincian</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            <a href="{{ route('dashboard', ['filter' => 'Musnah']) }}" class="group relative overflow-hidden bg-gradient-to-br from-rose-500 to-red-600 rounded-[2rem] p-8 text-white shadow-lg shadow-rose-500/10 hover:shadow-rose-500/25 hover:scale-[1.02] transition-all duration-300 min-h-[200px] flex flex-col justify-between">
                <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-white/10 rounded-full blur-2xl group-hover:scale-125 transition-transform"></div>
                <div class="flex items-start justify-between relative z-10">
                    <div>
                        <p class="text-rose-100 text-xs font-bold uppercase tracking-wider">Berkas Dimusnahkan</p>
                        <p class="text-5xl font-black mt-2">{{ $stats['Musnah'] }}</p>
                    </div>
                    <div class="w-14 h-14 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                </div>
                <div class="relative z-10 pt-4 flex items-center gap-1.5 text-rose-100 font-bold text-xs uppercase group-hover:translate-x-1 transition-transform">
                    <span>Lihat Rincian</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            <a href="{{ auth()->user()->isAdmin() ? route('destruction.requests') : route('dashboard') }}" class="group relative overflow-hidden bg-gradient-to-br from-violet-500 to-purple-600 rounded-[2rem] p-8 text-white shadow-lg shadow-violet-500/10 hover:shadow-violet-500/25 hover:scale-[1.02] transition-all duration-300 min-h-[200px] flex flex-col justify-between">
                <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-white/10 rounded-full blur-2xl group-hover:scale-125 transition-transform"></div>
                <div class="flex items-start justify-between relative z-10">
                    <div>
                        <p class="text-violet-100 text-xs font-bold uppercase tracking-wider">Pending Musnah</p>
                        <p class="text-5xl font-black mt-2">{{ $stats['Pending'] }}</p>
                    </div>
                    <div class="w-14 h-14 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </div>
                <div class="relative z-10 pt-4 flex items-center gap-1.5 text-violet-100 font-bold text-xs uppercase group-hover:translate-x-1 transition-transform">
                    <span>Lihat Rincian</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
        </div>

        {{-- Alerts --}}
        <div class="bg-white rounded-[2rem] border border-slate-100 shadow-sm p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Peringatan Jatuh Tempo Retensi</h2>
                    <p class="text-slate-500 text-sm mt-1">Berkas yang mendekati atau sudah melewati jatuh tempo</p>
                </div>
                <a href="{{ route('alerts') }}" class="px-4 py-2 bg-blue-50 text-blue-600 rounded-xl text-sm font-bold hover:bg-blue-100 transition-colors">Lihat Semua</a>
            </div>
            @forelse ($alerts as $alert)
                <div class="flex justify-between items-center py-4 border-b border-slate-100 last:border-0 hover:bg-slate-50/50 px-3 -mx-3 rounded-xl transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr($alert->nama_pasien, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-bold text-slate-800">{{ $alert->nama_pasien }} <span class="text-slate-400 font-normal">({{ $alert->no_rm }})</span></p>
                            <p class="text-xs text-slate-500">Jatuh tempo: <span class="text-red-600 font-semibold">{{ $alert->tgl_retensi?->format('d/m/Y') }}</span></p>
                        </div>
                    </div>
                    <a href="{{ route('berkas.show', $alert) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700">Detail</a>
                </div>
            @empty
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-slate-400 text-sm">Tidak ada peringatan saat ini.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="text-center text-slate-400 text-xs mt-12 py-4 border-t border-slate-200/60 max-w-7xl mx-auto">
        &copy; {{ date('Y') }} {{ config('app.name') }} — Klinik Kolbu.
    </div>
</div>
