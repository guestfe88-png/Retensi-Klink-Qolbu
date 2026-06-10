<?php

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public bool $showForm = false;
    public ?int $userId = null;
    public string $username = '';
    public string $email = '';
    public string $nama_lengkap = '';
    public string $role = 'petugas';
    public string $password = '';

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);
        $this->userId = $user->id;
        $this->username = $user->username;
        $this->email = $user->email ?? '';
        $this->nama_lengkap = $user->nama_lengkap ?? '';
        $this->role = $user->role;
        $this->password = '';
        $this->showForm = true;
    }

    public function save(AuditService $auditService): void
    {
        $rules = [
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($this->userId)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->userId)],
            'nama_lengkap' => 'required|string|max:100',
            'role' => 'required|in:admin,petugas',
            'password' => $this->userId ? ['nullable', Password::min(8)] : ['required', Password::min(8)],
        ];

        $this->validate($rules);

        $data = [
            'username' => $this->username,
            'email' => $this->email,
            'nama_lengkap' => $this->nama_lengkap,
            'role' => $this->role,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->userId) {
            $user = User::findOrFail($this->userId);
            $this->authorize('update', $user);
            $old = $user->toArray();
            $user->update($data);
            $auditService->log('user_updated', $user, $old, $user->fresh()->toArray());
        } else {
            $this->authorize('create', User::class);
            $user = User::create($data);
            $auditService->log('user_created', $user, null, $user->toArray());
        }

        $this->showForm = false;
        $this->resetForm();
        session()->flash('success', 'User berhasil disimpan.');
    }

    public function delete(int $id, AuditService $auditService): void
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);
        $old = $user->toArray();
        $user->delete();
        $auditService->log('user_deleted', $user, $old, null);
        session()->flash('success', 'User berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->userId = null;
        $this->username = '';
        $this->email = '';
        $this->nama_lengkap = '';
        $this->role = 'petugas';
        $this->password = '';
    }

    public function with(): array
    {
        return ['users' => User::orderBy('nama_lengkap')->paginate(10)];
    }
};
?>

<div class="p-6 flex-1 overflow-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manajemen User</h1>
        <button wire:click="create" class="px-4 py-2 bg-blue-600 text-white rounded-xl font-bold text-sm">Tambah User</button>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-emerald-50 rounded-2xl text-emerald-700">{{ session('success') }}</div>
    @endif

    @if ($showForm)
        <div class="mb-6 bg-white border rounded-2xl p-6">
            <h2 class="font-bold mb-4">{{ $userId ? 'Edit User' : 'Tambah User' }}</h2>
            <form wire:submit="save" class="grid md:grid-cols-2 gap-4">
                <input wire:model="username" placeholder="Username" class="px-4 py-2 border rounded-xl">
                <input wire:model="email" type="email" placeholder="Email" class="px-4 py-2 border rounded-xl">
                <input wire:model="nama_lengkap" placeholder="Nama Lengkap" class="px-4 py-2 border rounded-xl">
                <select wire:model="role" class="px-4 py-2 border rounded-xl">
                    <option value="petugas">Petugas</option>
                    <option value="admin">Admin</option>
                </select>
                <input wire:model="password" type="password" placeholder="{{ $userId ? 'Password baru (opsional)' : 'Password' }}" class="px-4 py-2 border rounded-xl md:col-span-2">
                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl font-bold">Simpan</button>
                    <button type="button" wire:click="$set('showForm', false)" class="px-4 py-2 bg-slate-100 rounded-xl font-bold">Batal</button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-white rounded-2xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Username</th>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-bold">{{ $user->username }}</td>
                        <td class="px-4 py-3">{{ $user->nama_lengkap }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ $user->role }}</td>
                        <td class="px-4 py-3 flex gap-2">
                            <button wire:click="edit({{ $user->id }})" class="text-blue-600 font-bold text-xs">Edit</button>
                            @can('delete', $user)
                                <button wire:click="delete({{ $user->id }})" wire:confirm="Hapus user?" class="text-red-600 font-bold text-xs">Hapus</button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $users->links() }}</div>
    </div>
</div>
