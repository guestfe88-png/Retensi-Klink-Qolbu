<?php

use Livewire\Component;
use Illuminate\Support\Facades\Password;

new class extends Component
{
    public string $email = '';
    public ?string $statusMessage = null;

    public function sendResetLink()
    {
        $this->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->statusMessage = 'Link reset password telah dikirim ke email Anda.';
            return;
        }

        $this->addError('email', __($status));
    }
};
?>

<div class="min-h-screen flex items-center justify-center bg-slate-900 px-4">
    <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Klinik Kolbu Logo" class="h-16 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-slate-800">LUPA PASSWORD</h1>
            <p class="text-slate-500 mt-2 text-sm">Masukkan email terdaftar untuk reset password</p>
        </div>

        @if ($statusMessage)
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-3 rounded-2xl text-sm mb-4">{{ $statusMessage }}</div>
        @else
            <form wire:submit="sendResetLink" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Email</label>
                    <input type="email" wire:model="email" required class="w-full px-4 py-3 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none">
                    @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-2xl font-bold">KIRIM LINK RESET</button>
            </form>
        @endif

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm font-bold text-slate-500 hover:text-slate-800">Kembali ke Login</a>
        </div>
    </div>
</div>
