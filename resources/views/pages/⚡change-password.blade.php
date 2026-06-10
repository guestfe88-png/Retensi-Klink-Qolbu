<?php

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

new class extends Component
{
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function changePassword()
    {
        $this->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = Auth::user();

        if (! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Password saat ini salah.');
            return;
        }

        $user->update(['password' => Hash::make($this->new_password)]);

        session()->flash('success', 'Password berhasil diperbarui!');

        return redirect()->route('home');
    }
};
?>

<div class="p-8 flex-1 overflow-auto">
    <div class="max-w-md mx-auto bg-white rounded-3xl shadow-xl border border-slate-100">
        <div class="px-6 py-5 border-b border-slate-100">
            <h3 class="text-xl font-bold text-slate-800">Ganti Password</h3>
        </div>
        <form wire:submit="changePassword" class="p-6 space-y-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Password Saat Ini</label>
                <input type="password" wire:model="current_password" required class="w-full px-4 py-3 border-2 border-slate-100 rounded-2xl">
                @error('current_password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Password Baru (min. 8 karakter, huruf + angka)</label>
                <input type="password" wire:model="new_password" required class="w-full px-4 py-3 border-2 border-slate-100 rounded-2xl">
                @error('new_password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Konfirmasi Password Baru</label>
                <input type="password" wire:model="new_password_confirmation" required class="w-full px-4 py-3 border-2 border-slate-100 rounded-2xl">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-2xl font-bold">Simpan</button>
        </form>
    </div>
</div>
