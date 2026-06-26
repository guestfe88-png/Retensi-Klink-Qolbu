# Deploy Retensi RM ke Railway (Gratis / Murah)

Repo: **https://github.com/guestfe88-png/Retensi-Klink-Qolbu**

---

## Persiapan

- Akun GitHub (sudah ada)
- Akun Railway: https://railway.app → **Login with GitHub**
- Repo sudah di-push ke GitHub

---

## Langkah 1 — Buat project di Railway

1. Buka https://railway.app/dashboard
2. Klik **New Project**
3. Pilih **Deploy from GitHub repo**
4. Authorize Railway akses GitHub (jika diminta)
5. Pilih repo **`Retensi-Klink-Qolbu`**
6. Tunggu build pertama (bisa 5–10 menit)

---

## Langkah 2 — Tambah database MySQL

1. Di project Railway, klik **+ New**
2. Pilih **Database** → **MySQL**
3. Tunggu sampai status **Active**
4. Klik service MySQL → tab **Variables** / **Connect**
5. Catat nilai:
   - `MYSQLHOST`
   - `MYSQLPORT`
   - `MYSQLDATABASE`
   - `MYSQLUSER`
   - `MYSQLPASSWORD`

---

## Langkah 3 — Set environment variables (service Laravel)

Klik service **web/app** (bukan MySQL) → **Variables** → **RAW Editor**, paste:

```env
APP_NAME=Retensi RM
APP_ENV=production
APP_DEBUG=false
APP_KEY=ISI_DENGAN_KEY_DARI_ARTISAN
APP_URL=https://ISI-DOMAIN-RAILWAY.up.railway.app

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
SESSION_ENCRYPT=true
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
LOG_LEVEL=error
```

> Ganti `MySQL` jika nama service database Anda berbeda (lihat di Railway dashboard).

### Generate APP_KEY

Di laptop, jalankan:

```powershell
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan key:generate --show
```

Copy hasilnya ke `APP_KEY=` di Railway.

### APP_URL

Setelah deploy, Railway beri domain seperti:
`https://retensi-klink-qolbu-production.up.railway.app`

Copy ke `APP_URL` (pakai **https**).

---

## Langkah 4 — Generate domain publik

1. Klik service Laravel → **Settings**
2. Bagian **Networking** → **Generate Domain**
3. Copy URL domain → update `APP_URL` di Variables
4. Klik **Redeploy** agar config terbaca ulang

---

## Langkah 5 — Cek deploy

1. Buka domain Railway di browser
2. Login:
   - Username: `admin`
   - Password: `admin123`
3. **Segera ganti password** setelah login!

---

## Update aplikasi (setelah ada perubahan code)

```powershell
git add .
git commit -m "Update fitur"
git push origin main
```

Railway akan **otomatis redeploy** dari GitHub.

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Build gagal | Cek **Deployments** → **View Logs** |
| 500 error | Pastikan `APP_KEY` sudah diisi |
| Database error | Cek variable `DB_*` dan nama service MySQL |
| CSS hilang | Pastikan `npm run build` sukses di log build |
| Upload PDF hilang setelah redeploy | Normal di Railway free — file tidak permanen* |

\*Untuk production serius, tambahkan **Railway Volume** mount ke `/app/storage/app` atau gunakan S3.

---

## Biaya

- Railway memberi **kredit gratis** (~$5/bulan)
- Aplikasi kecil biasanya cukup untuk demo/internal klinik
- Setelah kredit habis: ~$5/bulan atau upgrade

---

## Ringkasan arsitektur

```
GitHub (Retensi-Klink-Qolbu)
        ↓ auto deploy
Railway Web Service (Laravel + PHP 8.3)
        ↓
Railway MySQL (retensirm)
```
