# Retensi RM — Klinik Kolbu

Sistem manajemen retensi rekam medis berbasis Laravel 13 + Livewire 4.  
Khusus unit **Rawat Jalan**.

> Panduan instalasi : lihat **[PANDUAN-INSTALASI.md](PANDUAN-INSTALASI.md)**

## Fitur

- Autentikasi dengan rate limiting
- CRUD berkas rekam medis + upload PDF (private storage)
- Entitas pasien terpisah
- Aturan retensi otomatis per klasifikasi
- Peringatan jatuh tempo retensi
- Workflow persetujuan pemusnahan + sertifikat
- Audit log
- Role admin / petugas
- Manajemen user (admin)
- Export CSV
- API read-only (token)
- Scheduler retensi harian

## Persyaratan

- PHP 8.3+
- Composer
- MySQL
- Node.js 18+

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
# Atur DB_* di .env
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

## Login Default

- Username: `admin`
- Password: `admin123`

## Scheduler

Tambahkan ke cron:

```bash
php artisan schedule:run
```

Atau jalankan manual:

```bash
php artisan retensi:process
```

## API

Set `RETENSI_API_TOKEN` di `.env`, lalu:

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8000/api/berkas
```
