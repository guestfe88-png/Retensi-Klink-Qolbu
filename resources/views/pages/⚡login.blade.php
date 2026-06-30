<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

new class extends Component
{
    public string $username = '';
    public string $password = '';

    public function login()
    {
        $this->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $key = 'login:'.Str::lower($this->username).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (! Auth::attempt(['username' => $this->username, 'password' => $this->password])) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'username' => 'Username atau password salah.',
            ]);
        }

        RateLimiter::clear($key);
        session()->regenerate();

        return redirect()->route('home');
    }
};
?>

<div class="min-h-screen flex items-center justify-center bg-slate-900 px-4 relative overflow-hidden">
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/30 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-600/30 rounded-full blur-3xl"></div>

    <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-md relative z-10">
        <div class="text-center mb-8">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="h-20 object-contain mx-auto mb-5">
            <p class="text-xl font-bold text-slate-800 tracking-tight">Selamat Datang di</p>
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight mt-1">{{ config('app.name') }}</h1>
            <p class="text-slate-500 mt-2 text-sm">Silahkan masuk untuk melanjutkan</p>
        </div>

        @if (session()->has('success'))
            <div class="bg-green-50 border border-green-100 text-green-700 px-4 py-3 rounded-2xl text-sm font-medium mb-5">
                {{ session('success') }}
            </div>
        @endif

        <form wire:submit="login" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
                <input type="text" wire:model="username" required
                       class="w-full px-4 py-3 bg-slate-100 border-2 border-slate-100 rounded-2xl text-slate-800 placeholder:text-slate-400 outline-none focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100"
                       placeholder="Masukkan username">
                @error('username') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-semibold text-slate-700">Password</label>
                    <a href="{{ route('password.request') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">Lupa Password?</a>
                </div>
                <input type="password" wire:model="password" required
                       class="w-full px-4 py-3 bg-slate-100 border-2 border-slate-100 rounded-2xl text-slate-800 placeholder:text-slate-400 outline-none focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100"
                       placeholder="Masukkan password">
                @error('password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-3.5 rounded-2xl font-bold shadow-lg">
                MASUK
            </button>
        </form>
    </div>
</div>
