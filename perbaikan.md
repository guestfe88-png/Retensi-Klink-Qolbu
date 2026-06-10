# Catatan Perbaikan — Sistem Retensi RM (Klinik Kolbu)

Dokumen ini mencatat fitur yang **belum ada**, **belum lengkap**, atau **perlu diperbaiki** pada aplikasi Retensi RM.

**Status aplikasi saat ini:** Diperbarui — fitur prioritas tinggi & menengah telah diimplementasikan (Juni 2026).

**Stack:** Laravel 13, Livewire 4, MySQL (`retensirm`), Tailwind CSS 4

## Perbaikan yang Sudah Diterapkan

- [x] PDF aman (private storage + route auth)
- [x] Role admin/petugas + policies + manajemen user
- [x] Aturan retensi otomatis + scheduler `retensi:process`
- [x] Peringatan jatuh tempo retensi
- [x] Workflow persetujuan pemusnahan + sertifikat
- [x] Audit log
- [x] Soft delete berkas
- [x] Entitas pasien terpisah
- [x] Field nama_berkas, klasifikasi, lokasi arsip
- [x] Halaman detail berkas
- [x] Export CSV
- [x] API token (`/api/berkas`)
- [x] Logo SVG, sidebar responsif, sorting, bulk delete
- [x] Rate limiting login, password kuat, hapus demo credentials
- [x] Reset password via broker bawaan Laravel
- [x] Tests + README proyek

## Belum Diimplementasikan (Prioritas Rendah)

- [ ] Integrasi SIMRS/HIS penuh
- [ ] Notifikasi email/SMS otomatis
- [ ] Multi-klinik / multi-tenant

---

## Yang Sudah Ada

- Login / logout, lupa password, ganti password
- Dashboard dengan filter status (Aktif / Inaktif / Musnah) dan pencarian
- Tambah, edit, hapus berkas rekam medis + upload PDF
- Statistik ringkas di halaman Home
- User admin bawaan (`admin` / `admin123`) via seeder

---

## 1. Inti Sistem Retensi (Belum Ada)

| No | Item | Keterangan |
|----|------|------------|
| 1.1 | Aturan retensi otomatis | Status masih diisi manual. Belum ada aturan seperti: aktif 2 tahun → inaktif → musnah setelah X tahun |
| 1.2 | Perhitungan tanggal retensi | Field `tgl_retensi` dipakai sebagai "Terakhir Kunjungan", bukan tanggal jatuh tempo retensi |
| 1.3 | Scheduler / cron job | Tidak ada proses otomatis yang mengubah status atau mengingatkan berkas yang sudah waktunya dimusnahkan |
| 1.4 | Notifikasi / alert | Tidak ada peringatan "berkas akan musnah dalam 30 hari" |
| 1.5 | Workflow persetujuan | Status `Musnah` bisa langsung diubah tanpa persetujuan atau bukti pemusnahan |
| 1.6 | Audit trail | Tidak tercatat siapa mengubah/menghapus data dan kapan |
| 1.7 | Laporan & export | Tidak ada export Excel/PDF untuk kebutuhan audit atau pelaporan |
| 1.8 | Sertifikat pemusnahan | Tidak ada dokumentasi resmi saat berkas dimusnahkan |
| 1.9 | Legal hold | Tidak ada mekanisme menahan pemusnahan untuk kasus tertentu |
| 1.10 | Kepatuhan regulasi | Aturan UU PDP / Permenkes retensi RM belum dimodelkan di schema atau logika |

---

## 2. Keamanan & Hak Akses

| No | Item | Keterangan | Lokasi terkait |
|----|------|------------|----------------|
| 2.1 | Role tidak dipakai | Kolom `admin` / `petugas` ada di database, tapi tidak dicek di kode — semua user bisa edit/hapus semua data | `app/Models/User.php`, semua halaman Livewire |
| 2.2 | Manajemen user | Tidak ada halaman tambah/edit petugas | — |
| 2.3 | PDF tidak aman | File PDF diakses lewat URL publik (`storage/berkas/...`) — siapa pun yang punya link bisa buka tanpa login | `resources/views/pages/⚡dashboard.blade.php` |
| 2.4 | Tidak ada rate limiting login | Rentan brute force | `resources/views/pages/⚡login.blade.php` |
| 2.5 | Kredensial demo di halaman login | `admin / admin123` ditampilkan di UI | `resources/views/pages/⚡login.blade.php` |
| 2.6 | Hapus permanen | Data dan file langsung dihapus, tanpa soft delete atau arsip | `resources/views/pages/⚡dashboard.blade.php` |
| 2.7 | Tidak ada authorization policy | Folder `app/Policies/` kosong, tidak ada pemanggilan `authorize()` | — |
| 2.8 | Session tidak dienkripsi | `SESSION_ENCRYPT=false` di `.env.example` | `.env.example` |
| 2.9 | Reset password custom | Alur lupa password membuat token manual, melewati throttling bawaan Laravel | `resources/views/pages/⚡forgot-password.blade.php` |

---

## 3. Data & Validasi

| No | Item | Keterangan | Lokasi terkait |
|----|------|------------|----------------|
| 3.1 | `no_rm` tidak unik | Satu pasien bisa punya banyak entri duplikat | `database/migrations/2026_06_03_162431_create_berkas_table.php` |
| 3.2 | Tidak ada entitas Pasien terpisah | Data pasien (nama, alamat, dll.) disalin per baris berkas | `app/Models/Berkas.php` |
| 3.3 | Field `nama_berkas` tidak ada di form | Ada di database & validasi PHP, tapi tidak ada input di UI — selalu kosong | `resources/views/pages/⚡berkas-form.blade.php` |
| 3.4 | Tidak ada klasifikasi berkas | Misalnya rawat jalan, rawat inap, lab — hanya field `keterangan` bebas | — |
| 3.5 | Tidak ada lokasi fisik arsip | Rak, box, ruang arsip belum dicatat | — |
| 3.6 | Tidak ada halaman detail berkas | Hanya tabel + edit, tidak ada view read-only | — |
| 3.7 | Validasi password lemah | Minimal 6 karakter saja pada reset/ganti password | `⚡change-password.blade.php`, `⚡reset-password.blade.php` |
| 3.8 | Upload PDF minimal | Hanya cek mime + ukuran 5MB, tanpa inspeksi konten | `resources/views/pages/⚡berkas-form.blade.php` |
| 3.9 | Autofill pasien rentan error | Nama/alamat dengan tanda kutip bisa rusak saat autofill | `resources/views/pages/⚡berkas-form.blade.php` |
| 3.10 | `email_verified_at` orphan | Ada di model & factory, tapi kolom tidak ada di migrasi | `app/Models/User.php`, `database/factories/UserFactory.php` |

---

## 4. UI/UX

| No | Item | Keterangan | Lokasi terkait |
|----|------|------------|----------------|
| 4.1 | Logo hilang | `public/assets/images/logo.png` tidak ada di proyek | `resources/views/layouts/app.blade.php` |
| 4.2 | Sidebar tidak responsif | Tidak ada menu mobile / hamburger | `resources/views/layouts/app.blade.php` |
| 4.3 | Menu Dashboard dikomentari | Link utama Dashboard di sidebar di-comment | `resources/views/layouts/app.blade.php` baris 42–47 |
| 4.4 | Tidak ada sorting kolom | Tabel hanya urut `created_at desc` | `resources/views/pages/⚡dashboard.blade.php` |
| 4.5 | Tidak ada bulk action | Tidak bisa ubah status/hapus banyak data sekaligus | `resources/views/pages/⚡dashboard.blade.php` |
| 4.6 | Kolom tabel terbatas | `nama_berkas`, `keterangan`, `created_by` tidak ditampilkan di dashboard | `resources/views/pages/⚡dashboard.blade.php` |
| 4.7 | Login redirect ke dashboard | Entry point tidak konsisten dengan menu Home | `resources/views/pages/⚡login.blade.php` |
| 4.8 | Hardcoded URL | Form memakai path langsung (`/dashboard`) bukan `route()` helper | Beberapa halaman Livewire |
| 4.9 | Class Tailwind tidak valid | `text-red-650`, `text-slate-750`, `to-teal-650` kemungkinan tidak berpengaruh | Beberapa halaman |
| 4.10 | Font tidak konsisten | Layout pakai Plus Jakarta Sans, tema CSS pakai Instrument Sans | `layouts/app.blade.php`, `resources/css/app.css` |

---

## 5. Teknis & Pengembangan

| No | Item | Keterangan |
|----|------|------------|
| 5.1 | Testing minim | Hanya tes Home & halaman login; tidak ada tes CRUD berkas, auth, dashboard |
| 5.2 | Tidak ada `BerkasFactory` | Sulit menulis test untuk fitur berkas |
| 5.3 | Dokumentasi proyek | `README.md` masih default Laravel |
| 5.4 | Duplikat folder proyek | Ada salinan di `RETENSIRM-main/RETENSIRM-main/` — kemungkinan hasil ekstrak arsip |
| 5.5 | Halaman `welcome.blade.php` | Tidak dipakai, sisa starter Laravel |
| 5.6 | Tidak ada API | Tidak ada `routes/api.php` untuk integrasi eksternal |
| 5.7 | Queue & jobs tidak dipakai | Tabel jobs ada, tapi tidak ada background job untuk retensi |

---

## 6. Skema Database Saat Ini

### Tabel `users`
- `username`, `email`, `password`, `nama_lengkap`, `role` (`admin` / `petugas`)
- Role **belum diterapkan** di aplikasi

### Tabel `berkas` (entitas utama)
- `no_rm`, `nama_pasien`, `tgl_lahir`, `alamat`, `nama_berkas`, `file_pdf`
- `status` (`Aktif` / `Inaktif` / `Musnah`) — manual
- `tgl_retensi` — dipakai sebagai terakhir kunjungan
- `keterangan`, `created_by`

### Relasi
```
users (1) ──< berkas.created_by
```

### Entitas yang belum ada
- `patients` (pasien terpisah)
- `retention_policies` (aturan retensi)
- `audit_logs` (jejak aktivitas)
- `destruction_certificates` (bukti pemusnahan)
- `archive_locations` (lokasi fisik)

---

## 7. Prioritas Perbaikan

### Prioritas Tinggi (sebelum produksi)

1. Amankan akses PDF — download hanya untuk user yang sudah login
2. Terapkan hak akses `admin` vs `petugas` + halaman manajemen user
3. Buat aturan retensi otomatis + alert jatuh tempo
4. Tambah audit log dan soft delete untuk data musnah
5. Perbaiki bug: field `nama_berkas` di form, logo hilang, validasi `no_rm` unik

### Prioritas Menengah

6. Laporan / export Excel & PDF
7. Workflow persetujuan pemusnahan + sertifikat musnah
8. Halaman detail berkas + lokasi fisik arsip
9. Entitas Pasien terpisah dari Berkas
10. Perbaiki sidebar responsif & konsistensi navigasi

### Prioritas Rendah

11. Integrasi SIMRS / HIS
12. Notifikasi email / SMS
13. API untuk integrasi eksternal
14. Multi-klinik / multi-tenant

---

## 8. Catatan Tambahan

- Aplikasi aktif berada di root folder `d:\Project\Retensi`
- Duplikat di `RETENSIRM-main/RETENSIRM-main/` tidak perlu dipakai
- Mail driver default: `log` — reset password tidak terkirim ke email nyata di development
- Login default seeder: username `admin`, password `admin123`, email `admin@example.com`

---

*Terakhir diperbarui: Juni 2026*
