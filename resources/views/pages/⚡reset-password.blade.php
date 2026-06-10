<?php

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

new class extends Component
{
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $errorMessage = null;

    public function mount($token)
    {
        $this->token = $token;
        $this->email = request()->query('email', '');
    }

    public function resetPassword()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
            'password_confirmation' => 'required',
        ]);

        $status = Password::broker()->reset(
            [
                'token' => $this->token,
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            session()->flash('success', 'Password Anda berhasil diperbarui! Silakan masuk.');
            return redirect()->route('login');
        }

        $this->errorMessage = match ($status) {
            Password::INVALID_USER => 'Pengguna dengan email tersebut tidak ditemukan.',
            Password::INVALID_TOKEN => 'Token reset password tidak valid atau sudah kadaluwarsa.',
            default => 'Gagal mereset password. Silakan coba lagi.'
        };
    }
};
?>

<div class="min-h-screen flex items-center justify-center bg-slate-900 px-4 relative overflow-hidden">
    <!-- Abstract glowing blobs for premium feel -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-600/30 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-indigo-600/30 rounded-full blur-3xl"></div>

    <div class="bg-white/80 backdrop-blur-md rounded-3xl shadow-2xl p-8 w-full max-w-md border border-white/20 relative z-10">
        <div class="text-center mb-8">
            <div class="mb-4">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Klinik Kolbu Logo" class="h-20 object-contain mx-auto">
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">ATUR ULANG PASSWORD</h1>
            <p class="text-slate-500 mt-2 text-sm font-medium">Buat password baru yang aman untuk akun Anda</p>
        </div>
        
        <form wire:submit="resetPassword" class="space-y-5">
            <!-- Email (Read-only for security & accuracy, or editable if they prefer) -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Email</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <input type="email" wire:model="email" required 
                           class="w-full pl-12 pr-4 py-3 bg-slate-100 border-2 border-slate-200 rounded-2xl outline-none text-slate-500 font-semibold cursor-not-allowed"
                           readonly>
                </div>
                @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Password Baru</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                    <input type="password" wire:model="password" required 
                           class="w-full pl-12 pr-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 outline-none transition-all duration-200 text-slate-800"
                           placeholder="Masukkan password baru">
                </div>
                @error('password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Konfirmasi Password Baru</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                    <input type="password" wire:model="password_confirmation" required 
                           class="w-full pl-12 pr-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 outline-none transition-all duration-200 text-slate-800"
                           placeholder="Ulangi password baru">
                </div>
                @error('password_confirmation') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            @if ($errorMessage)
                <div class="bg-red-50 border border-red-100 text-red-655 px-4 py-3 rounded-2xl text-sm font-medium">
                    {{ $errorMessage }}
                </div>
            @endif

            <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-3.5 rounded-2xl font-bold shadow-lg hover:shadow-blue-500/20 hover:shadow-xl transition-all duration-300 transform active:scale-95">
                SIMPAN PASSWORD BARU
            </button>
        </form>
        
        <div class="mt-8 pt-6 border-t border-slate-100 text-center">
            <a href="/login" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali ke Login</span>
            </a>
        </div>
    </div>
</div>
