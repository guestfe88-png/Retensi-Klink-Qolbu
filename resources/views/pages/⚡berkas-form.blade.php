<?php

use App\Models\Berkas;
use App\Models\Patient;
use App\Services\AuditService;
use App\Services\PatientService;
use App\Services\RetentionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public ?int $berkasId = null;
    public string $no_rm = '';
    public string $nama_pasien = '';
    public ?string $tgl_lahir = null;
    public string $alamat = '';
    public string $nama_berkas = '';
    public string $klasifikasi = 'rawat_jalan';
    public string $lokasi_arsip = '';
    public string $status = 'Aktif';
    public ?string $tgl_kunjungan_terakhir = null;
    public string $keterangan = '';
    public bool $legal_hold = false;
    public $file_pdf;
    public ?string $existingFilePdf = null;
    public string $searchPatient = '';
    public array $patientSuggestions = [];
    public ?int $selectedPatientIndex = null;

    public function mount($id = null)
    {
        if ($id) {
            $berkas = Berkas::findOrFail($id);
            $this->authorize('update', $berkas);

            $this->berkasId = $berkas->id;
            $this->no_rm = $berkas->no_rm;
            $this->nama_pasien = $berkas->nama_pasien;
            $this->tgl_lahir = $berkas->tgl_lahir?->format('Y-m-d');
            $this->alamat = $berkas->alamat ?? '';
            $this->nama_berkas = $berkas->nama_berkas ?? '';
            $this->klasifikasi = Berkas::DEFAULT_KLASIFIKASI;
            $this->lokasi_arsip = $berkas->lokasi_arsip ?? '';
            $this->status = $berkas->status;
            $this->tgl_kunjungan_terakhir = $berkas->tgl_kunjungan_terakhir?->format('Y-m-d');
            $this->keterangan = $berkas->keterangan ?? '';
            $this->legal_hold = $berkas->legal_hold;
            $this->existingFilePdf = $berkas->file_pdf;
        } else {
            $this->authorize('create', Berkas::class);
        }
    }

    public function updatedSearchPatient($value)
    {
        if (empty($value)) {
            $this->patientSuggestions = [];
            return;
        }

        $this->patientSuggestions = Patient::query()
            ->where('nama_pasien', 'like', "%{$value}%")
            ->orWhere('no_rm', 'like', "%{$value}%")
            ->limit(5)
            ->get()
            ->map(fn ($p) => $p->only(['id', 'no_rm', 'nama_pasien', 'tgl_lahir', 'alamat']))
            ->toArray();
    }

    public function selectPatient(int $index)
    {
        $patient = $this->patientSuggestions[$index] ?? null;
        if (! $patient) {
            return;
        }

        $this->no_rm = $patient['no_rm'];
        $this->nama_pasien = $patient['nama_pasien'];
        $this->tgl_lahir = $patient['tgl_lahir'] ? \Carbon\Carbon::parse($patient['tgl_lahir'])->format('Y-m-d') : null;
        $this->alamat = $patient['alamat'] ?? '';
        $this->patientSuggestions = [];
        $this->searchPatient = '';
    }

    public function getUsia(): ?int
    {
        if (! $this->tgl_lahir) {
            return null;
        }

        return \Carbon\Carbon::parse($this->tgl_lahir)->age;
    }

    public function save(
        PatientService $patientService,
        RetentionService $retentionService,
        AuditService $auditService
    ) {
        $rules = [
            'no_rm' => ['required', 'string', 'max:20', Rule::unique('patients', 'no_rm')->ignore(
                Patient::where('no_rm', $this->no_rm)->value('id')
            )],
            'nama_pasien' => 'required|string|max:100',
            'tgl_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'nama_berkas' => 'nullable|string|max:100',
            'klasifikasi' => 'required|in:'.Berkas::DEFAULT_KLASIFIKASI,
            'lokasi_arsip' => 'nullable|string|max:150',
            'status' => 'required|in:Aktif,Inaktif,Musnah',
            'tgl_kunjungan_terakhir' => 'nullable|date',
            'keterangan' => 'nullable|string',
            'file_pdf' => 'nullable|file|mimes:pdf|max:5120',
            'legal_hold' => 'boolean',
        ];

        $this->klasifikasi = Berkas::DEFAULT_KLASIFIKASI;
        $this->validate($rules);

        if ($this->file_pdf && $this->file_pdf->getMimeType() !== 'application/pdf') {
            $this->addError('file_pdf', 'File harus berformat PDF yang valid.');
            return;
        }

        $berkas = $this->berkasId ? Berkas::findOrFail($this->berkasId) : new Berkas();
        $this->authorize($this->berkasId ? 'update' : 'create', $berkas);

        if ($this->status === 'Musnah' && ! Auth::user()->isAdmin()) {
            $this->addError('status', 'Hanya admin yang dapat langsung menetapkan status Musnah. Ajukan melalui persetujuan.');
            return;
        }

        if ($this->legal_hold && ! Auth::user()->isAdmin()) {
            $this->addError('legal_hold', 'Hanya admin yang dapat mengatur legal hold.');
            return;
        }

        $old = $berkas->exists ? $berkas->toArray() : null;
        $patient = $patientService->syncFromBerkas([
            'no_rm' => $this->no_rm,
            'nama_pasien' => $this->nama_pasien,
            'tgl_lahir' => $this->tgl_lahir,
            'alamat' => $this->alamat,
        ]);

        $filename = $this->existingFilePdf;
        if ($this->file_pdf) {
            if ($this->existingFilePdf) {
                Storage::disk('local')->delete('berkas/'.$this->existingFilePdf);
                Storage::disk('public')->delete('berkas/'.$this->existingFilePdf);
            }
            $filename = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $this->file_pdf->getClientOriginalName());
            $this->file_pdf->storeAs('berkas', $filename, 'local');
        }

        $berkas->fill([
            'patient_id' => $patient->id,
            'no_rm' => $this->no_rm,
            'nama_pasien' => $this->nama_pasien,
            'tgl_lahir' => $this->tgl_lahir,
            'alamat' => $this->alamat,
            'nama_berkas' => $this->nama_berkas,
            'klasifikasi' => Berkas::DEFAULT_KLASIFIKASI,
            'lokasi_arsip' => $this->lokasi_arsip,
            'file_pdf' => $filename,
            'status' => $this->status,
            'tgl_kunjungan_terakhir' => $this->tgl_kunjungan_terakhir,
            'keterangan' => $this->keterangan,
            'legal_hold' => $this->legal_hold,
        ]);

        if (! $this->berkasId) {
            $berkas->created_by = Auth::id();
        }

        $retentionService->recalculate($berkas);
        $berkas->save();

        $auditService->log($this->berkasId ? 'updated' : 'created', $berkas, $old, $berkas->fresh()->toArray());

        session()->flash('success', $this->berkasId ? 'Data berhasil diperbarui.' : 'Data berhasil ditambahkan.');

        return redirect()->route('berkas.show', $berkas);
    }
};
?>

<div class="p-6 flex-1 overflow-auto">
    <div class="max-w-3xl mx-auto bg-white rounded-3xl shadow-xl border border-slate-100">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-xl font-bold text-slate-800">{{ $berkasId ? 'Edit Rekam Medis' : 'Tambah Rekam Medis' }}</h3>
            <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600">Tutup</a>
        </div>

        <form wire:submit="save" class="p-6 space-y-5">
            @if (! $berkasId)
            <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100">
                <label class="block text-xs font-bold text-blue-800 uppercase mb-2">Cari Pasien Terdaftar</label>
                <input type="text" wire:model.live.debounce.300ms="searchPatient" class="w-full px-4 py-2.5 rounded-xl border border-blue-200" placeholder="No RM atau nama pasien...">
                @if (! empty($patientSuggestions))
                    <div class="mt-2 bg-white border rounded-xl divide-y">
                        @foreach ($patientSuggestions as $index => $patient)
                            <button type="button" wire:click="selectPatient({{ $index }})" class="w-full px-4 py-3 text-left hover:bg-blue-50">
                                <span class="font-bold">{{ $patient['nama_pasien'] }}</span>
                                <span class="text-xs text-slate-500 ml-2">RM: {{ $patient['no_rm'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">No RM *</label>
                    <input type="text" wire:model="no_rm" class="w-full px-4 py-3 border rounded-2xl">
                    @error('no_rm') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Nama Pasien *</label>
                    <input type="text" wire:model="nama_pasien" class="w-full px-4 py-3 border rounded-2xl">
                    @error('nama_pasien') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Nama Berkas</label>
                <input type="text" wire:model="nama_berkas" class="w-full px-4 py-3 border rounded-2xl" placeholder="Contoh: RM Rawat Jalan Jan 2026">
                @error('nama_berkas') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">Unit</label>
                    <div class="w-full px-4 py-3 border rounded-2xl bg-slate-50 text-slate-700 font-semibold">
                        Rawat Jalan
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Lokasi Arsip</label>
                    <input type="text" wire:model="lokasi_arsip" class="w-full px-4 py-3 border rounded-2xl" placeholder="Rak A / Box 12">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Alamat</label>
                <textarea wire:model="alamat" rows="2" class="w-full px-4 py-3 border rounded-2xl"></textarea>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">Tanggal Lahir @if($this->getUsia())<span class="text-xs text-blue-600">({{ $this->getUsia() }} th)</span>@endif</label>
                    <input type="date" wire:model.live="tgl_lahir" class="w-full px-4 py-3 border rounded-2xl">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Terakhir Kunjungan</label>
                    <input type="date" wire:model="tgl_kunjungan_terakhir" class="w-full px-4 py-3 border rounded-2xl">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">Status *</label>
                    <select wire:model="status" class="w-full px-4 py-3 border rounded-2xl" @disabled(!auth()->user()->isAdmin() && $status === 'Musnah')>
                        <option value="Aktif">Aktif</option>
                        <option value="Inaktif">Inaktif</option>
                        @if(auth()->user()->isAdmin())
                        <option value="Musnah">Musnah</option>
                        @endif
                    </select>
                    @error('status') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                @if(auth()->user()->isAdmin())
                <div class="flex items-end">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="legal_hold" class="rounded">
                        <span class="text-sm font-semibold">Legal Hold (tahan pemusnahan)</span>
                    </label>
                </div>
                @endif
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Keterangan</label>
                <textarea wire:model="keterangan" rows="3" class="w-full px-4 py-3 border rounded-2xl"></textarea>
            </div>

            <div class="border-2 border-dashed rounded-2xl p-4">
                <label class="block text-sm font-bold mb-2">Unggah PDF (maks 5MB)</label>
                @if ($existingFilePdf && ! $file_pdf)
                    <p class="text-sm text-slate-600 mb-2">File saat ini: {{ $existingFilePdf }}</p>
                @endif
                <input type="file" wire:model="file_pdf" accept=".pdf" class="w-full">
                @error('file_pdf') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="flex gap-3 pt-4 border-t">
                <a href="{{ route('dashboard') }}" class="flex-1 text-center py-3 bg-slate-100 rounded-2xl font-bold">Batal</a>
                <button type="submit" class="flex-1 py-3 bg-blue-600 text-white rounded-2xl font-bold">Simpan</button>
            </div>
        </form>
    </div>
</div>
