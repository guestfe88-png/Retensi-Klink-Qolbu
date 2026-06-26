# Panduan Instalasi Retensi RM — Klinik Kolbu

Panduan ini untuk belum pernah install aplikasi web.  
Ikuti langkah demi langkah dari atas ke bawah.

---

## Apa yang Anda butuhkan?

| No | Program | Fungsi |
|----|---------|--------|
| 1 | **Laragon** | Menjalankan PHP + MySQL di laptop |
| 2 | **Composer** | Menginstall library PHP (biasanya sudah ada di Laragon) |
| 3 | **Node.js** | Membangun tampilan web (CSS/JS) |
| 4 | **Folder project** | `Retensi` (sudah ada di laptop Anda) |

> **Catatan:** Aplikasi ini khusus unit **Rawat Jalan** saja.

---

## BAGIAN 1 — Install Laragon (sekali saja)

### Langkah 1.1 — Download Laragon

1. Buka browser → kunjungi: https://laragon.org/download/
2. Download **Laragon Full** (versi terbaru)
3. Jalankan file installer → klik **Next** sampai selesai
4. Buka program **Laragon**

### Langkah 1.2 — Nyalakan server

1. Di jendela Laragon, klik tombol **Start All**
2. Pastikan lampu **Apache** dan **MySQL** berwarna hijau / aktif

---

## BAGIAN 2 — Siapkan folder project

### Langkah 2.1 — Letakkan project

Pastikan folder project ada di:

```
D:\Project\Retensi
```

Atau salin ke folder Laragon:

```
C:\laragon\www\Retensi
```

(Keduanya boleh — panduan ini pakai `D:\Project\Retensi`)

---

## BAGIAN 3 — Buat database

### Langkah 3.1 — Buka HeidiSQL (dari Laragon)

1. Di Laragon, klik kanan → **MySQL** → **HeidiSQL**
2. Klik **Open** (koneksi default, password kosong)

### Langkah 3.2 — Buat database baru

1. Klik kanan pada koneksi kiri → **Create new** → **Database**
2. Nama database: `retensirm`
3. Klik **OK**

---

## BAGIAN 4 — Install aplikasi (lewat Terminal)

### Langkah 4.1 — Buka PowerShell

1. Tekan tombol **Windows**
2. Ketik `PowerShell` → Enter
3. Ketik perintah berikut (satu per satu, tekan Enter setelah tiap baris):

```powershell
cd D:\Project\Retensi
```

### Langkah 4.2 — Install library PHP

```powershell
composer install
```

Tunggu sampai selesai (bisa beberapa menit).

### Langkah 4.3 — Buat file pengaturan (.env)

```powershell
copy .env.example .env
```

### Langkah 4.4 — Generate kunci aplikasi

```powershell
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan key:generate
```

> Ganti path PHP di atas jika versi Laragon Anda berbeda.  
> Cek di folder: `C:\laragon\bin\php\`

### Langkah 4.5 — Atur database di file .env

1. Buka folder `D:\Project\Retensi`
2. Buka file `.env` dengan Notepad
3. Cari baris berikut dan pastikan isinya seperti ini:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=retensirm
DB_USERNAME=root
DB_PASSWORD=
```

4. Simpan file (Ctrl + S)

### Langkah 4.6 — Buat tabel database

```powershell
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan migrate --seed
```

Perintah ini membuat tabel dan user login awal.

### Langkah 4.7 — Build tampilan web

```powershell
npm install
npm run build
```

Tunggu sampai selesai.

---

## BAGIAN 5 — Jalankan aplikasi

### Langkah 5.1 — Start server

Di PowerShell (masih di folder project):

```powershell
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan serve
```

Jika berhasil, akan muncul teks:

```
Server running on [http://127.0.0.1:8000]
```

### Langkah 5.2 — Buka di browser

1. Buka **Chrome** atau **Edge**
2. Ketik di address bar:

```
http://127.0.0.1:8000
```

3. Tekan Enter

### Langkah 5.3 — Login

| Username | Password |
|----------|----------|
| `admin` | `admin123` |

atau

| Username | Password |
|----------|----------|
| `petugas` | `petugas123` |

> **Penting:** Segera ganti password setelah login pertama!

---

## BAGIAN 6 — Setiap hari pakai aplikasi

Urutan singkat setiap kali mau pakai:

1. Nyalakan laptop
2. Buka **Laragon** → klik **Start All**
3. Buka **PowerShell**:

```powershell
cd D:\Project\Retensi
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan serve
```

4. Buka browser → `http://127.0.0.1:8000`
5. **Jangan tutup** jendela PowerShell selama pakai aplikasi
6. Selesai pakai → tutup PowerShell (server berhenti)

---

## BAGIAN 7 — Cara pakai singkat

### Tambah berkas rekam medis

1. Login sebagai `admin` atau `petugas`
2. Klik menu **Tambah Berkas** / **Form Berkas**
3. Isi data pasien (No RM, nama, dll.)
4. Unit otomatis: **Rawat Jalan**
5. Upload PDF (opsional)
6. Klik **Simpan**

### Lihat daftar berkas

1. Klik **Dashboard**
2. Klik kartu **Aktif** / **Inaktif** / **Musnah** untuk filter

### Ganti password

1. Login sebagai **admin**
2. Buka menu **Manajemen User**
3. Edit user → ganti password

---

## Masalah umum & solusi

| Masalah | Solusi |
|---------|--------|
| `composer` tidak dikenali | Buka Laragon → Menu → Tools → Path → Add Laragon to Path, restart PowerShell |
| Error database / SQLSTATE | Pastikan Laragon **MySQL** sudah Start All, cek `.env` |
| Halaman putih / error 500 | Jalankan: `php artisan migrate --seed` ulang |
| CSS/tampilan berantakan | Jalankan: `npm run build` |
| Port 8000 sudah dipakai | Ganti port: `php artisan serve --port=8001` lalu buka `http://127.0.0.1:8001` |
| Laptop mati | Aplikasi **tidak jalan** — ulangi Bagian 6 |

---

## Ringkasan perintah (copy-paste)

```powershell
cd D:\Project\Retensi
composer install
copy .env.example .env
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan key:generate
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan migrate --seed
npm install
npm run build
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan serve
```

---

*Retensi RM — Klinik Kolbu — Unit Rawat Jalan*
