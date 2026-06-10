<?php

use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
    }

    public function with(): array
    {
        $query = AuditLog::with('user')->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('action', 'like', '%'.$this->search.'%')
                    ->orWhere('auditable_type', 'like', '%'.$this->search.'%');
            });
        }

        return ['logs' => $query->paginate(20)];
    }
};
?>

<div class="p-6 flex-1 overflow-auto">
    <h1 class="text-2xl font-bold mb-6">Audit Log</h1>
    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari aksi..." class="mb-4 px-4 py-2 border rounded-xl w-full max-w-md">

    <div class="bg-white rounded-2xl border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Waktu</th>
                    <th class="px-4 py-3 text-left">User</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                    <th class="px-4 py-3 text-left">Entitas</th>
                    <th class="px-4 py-3 text-left">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($logs as $log)
                    <tr>
                        <td class="px-4 py-3">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $log->user?->nama_lengkap ?? 'System' }}</td>
                        <td class="px-4 py-3 font-bold">{{ $log->action }}</td>
                        <td class="px-4 py-3">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                        <td class="px-4 py-3">{{ $log->ip_address }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $logs->links() }}</div>
    </div>
</div>
