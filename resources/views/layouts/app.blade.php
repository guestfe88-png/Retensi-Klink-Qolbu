<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Retensi Rekam Medis' }} - Klinik Kolbu</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full overflow-hidden text-slate-800 font-sans">
    <input type="checkbox" id="sidebar-toggle" class="peer hidden">
    <div class="flex h-full min-h-screen">
        @auth
        <label for="sidebar-toggle" class="fixed inset-0 z-40 bg-black/50 hidden peer-checked:block lg:hidden"></label>

        <aside class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-slate-900 text-white flex-shrink-0 flex flex-col border-r border-slate-800 transform -translate-x-full peer-checked:translate-x-0 lg:translate-x-0 transition-transform duration-200">
            <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center p-1.5 shadow-md group-hover:scale-105 transition-transform">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                    </div>
                    <div class="flex flex-col">
                        <span class="font-bold text-sm leading-none tracking-tight">Klinik Kolbu</span>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase mt-0.5 tracking-wider">Retensi RM</span>
                    </div>
                </a>
                <label for="sidebar-toggle" class="lg:hidden text-slate-400 cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </label>
            </div>

            <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
                <a href="{{ route('home') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('home') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="font-medium text-sm">Home</span>
                </a>
                <a href="{{ route('dashboard') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') && !request('filter') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span class="font-medium text-sm">Dashboard</span>
                </a>
                <a href="{{ route('alerts') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('alerts') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span class="font-medium text-sm">Peringatan Retensi</span>
                </a>
                <a href="{{ route('dashboard', ['filter' => 'Aktif']) }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request('filter') === 'Aktif' ? 'bg-slate-800 text-white border-l-4 border-green-500 pl-3' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-medium text-sm">Data Aktif</span>
                </a>
                <a href="{{ route('dashboard', ['filter' => 'Inaktif']) }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request('filter') === 'Inaktif' ? 'bg-slate-800 text-white border-l-4 border-amber-500 pl-3' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-medium text-sm">Data Inaktif</span>
                </a>
                <a href="{{ route('dashboard', ['filter' => 'Musnah']) }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request('filter') === 'Musnah' ? 'bg-slate-800 text-white border-l-4 border-red-500 pl-3' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span class="font-medium text-sm">Data Musnah</span>
                </a>

                @if(auth()->user()->isAdmin())
                <div class="pt-4 border-t border-slate-800 mt-4 space-y-1.5">
                    <p class="px-4 text-[10px] font-bold uppercase tracking-wider text-slate-500">Admin</p>
                    <a href="{{ route('destruction.requests') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('destruction.requests') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
                        <span class="font-medium text-sm">Persetujuan Musnah</span>
                    </a>
                    <a href="{{ route('users.index') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('users.index') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
                        <span class="font-medium text-sm">Manajemen User</span>
                    </a>
                    <a href="{{ route('retention.settings') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('retention.settings') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
                        <span class="font-medium text-sm">Aturan Retensi</span>
                    </a>
                    <a href="{{ route('audit-logs') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('audit-logs') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
                        <span class="font-medium text-sm">Audit Log</span>
                    </a>
                </div>
                @endif

                <a href="{{ route('change-password') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('change-password') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20' : 'hover:bg-slate-800/60 text-slate-400 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m-2 4a2 2 0 012 2m3 4H4a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2z"/></svg>
                    <span class="font-medium text-sm">Ganti Password</span>
                </a>

                <div class="pt-6 border-t border-slate-800 mt-6">
                    <a href="{{ route('berkas.create') }}" class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-semibold text-sm shadow-lg shadow-emerald-500/20 hover:scale-[1.02] transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        <span>Tambah Data</span>
                    </a>
                    <a href="{{ route('berkas.export') }}" class="mt-2 flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-semibold text-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Export CSV</span>
                    </a>
                </div>
            </nav>

            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                <div class="px-4 py-3 rounded-xl bg-slate-800/80 mb-3 border border-slate-700">
                    <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->nama_lengkap }}</p>
                    <span class="inline-block text-[10px] bg-blue-500/10 text-blue-400 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider mt-1 border border-blue-500/20">
                        {{ Auth::user()->role }}
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full group flex items-center gap-3 px-4 py-2.5 rounded-xl hover:bg-rose-500/10 text-slate-400 hover:text-rose-400 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span class="font-semibold text-sm">Keluar</span>
                    </button>
                </form>
            </div>
        </aside>
        @endauth

        <main class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50">
            @auth
            <div class="lg:hidden sticky top-0 z-30 bg-white border-b border-slate-200 px-4 py-3 flex items-center gap-3 shadow-sm">
                <label for="sidebar-toggle" class="p-2 rounded-lg hover:bg-slate-100 cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </label>
                <span class="font-bold text-slate-800">Retensi RM</span>
            </div>
            @endauth
            {{ $slot }}
        </main>
    </div>
</body>
</html>
