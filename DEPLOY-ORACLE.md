# Deploy Retensi RM ke Oracle Cloud (Gratis)

Panduan ini untuk menjalankan aplikasi Laravel Retensi di **Oracle Cloud Always Free** (VPS Ubuntu).

## Persyaratan

- Akun Oracle Cloud (gratis): https://www.oracle.com/cloud/free/
- Project Retensi sudah siap di laptop
- Akses SSH ke VM

---

## Langkah 1 — Buat VM di Oracle Cloud

1. Login **Oracle Cloud Console** → **Compute** → **Instances** → **Create Instance**
2. Pilih:
   - **Image:** Ubuntu 22.04 atau 24.04
   - **Shape:** `VM.Standard.A1.Flex` (Ampere — **Always Free**)
   - **OCPU:** 1–2, **Memory:** 6 GB (cukup untuk Laravel + MySQL)
3. **Networking:** centang **Assign a public IPv4 address**
4. **SSH keys:** upload public key Anda (atau generate & simpan private key)
5. Klik **Create**

### Buka port di firewall Oracle

1. **Networking** → **Virtual Cloud Networks** → pilih VCN instance Anda
2. **Security Lists** → **Default Security List** → **Add Ingress Rules**
3. Tambahkan:

| Source CIDR | Protocol | Port |
|-------------|----------|------|
| `0.0.0.0/0` | TCP | 22 |
| `0.0.0.0/0` | TCP | 80 |
| `0.0.0.0/0` | TCP | 443 |

---

## Langkah 2 — SSH ke server

```bash
ssh -i path/to/private_key ubuntu@IP_PUBLIK_SERVER
```

Ganti `ubuntu` jika user image berbeda (mis. `opc`).

---

## Langkah 3 — Setup server (sekali saja)

### Opsi A: Upload project dulu, lalu jalankan script

Dari laptop (PowerShell):

```powershell
scp -i path/to/private_key -r d:\Project\Retensi ubuntu@IP_PUBLIK:/tmp/retensi
```

Di server:

```bash
sudo mv /tmp/retensi /var/www/retensi
cd /var/www/retensi
sudo chmod +x deploy/oracle/*.sh
sudo bash deploy/oracle/setup-server.sh
```

> **Penting:** Edit password MySQL di `setup-server.sh` baris `GANTI_PASSWORD_KUAT` sebelum dijalankan, atau ubah manual setelahnya.

### Opsi B: Clone dari Git (jika sudah push ke GitHub)

```bash
sudo git clone https://github.com/USERNAME/retensi.git /var/www/retensi
cd /var/www/retensi
sudo chmod +x deploy/oracle/*.sh
sudo bash deploy/oracle/setup-server.sh
```

---

## Langkah 4 — Konfigurasi environment

```bash
cd /var/www/retensi
sudo cp .env.production.example .env
sudo nano .env
```

Isi minimal:

```env
APP_URL=http://IP_PUBLIK_SERVER
APP_KEY=                    # generate di bawah
DB_DATABASE=retensirm
DB_USERNAME=retensi
DB_PASSWORD=password_yang_sama_dengan_mysql
```

Generate `APP_KEY`:

```bash
php artisan key:generate
```

---

## Langkah 5 — Deploy aplikasi

```bash
cd /var/www/retensi
sudo bash deploy/oracle/deploy-app.sh
```

Seed user admin (sekali):

```bash
php artisan db:seed --force
```

Login default:
- Username: `admin` / Password: `admin123`
- **Segera ganti password setelah login!**

---

## Langkah 6 — Akses aplikasi

Buka browser:

```
http://IP_PUBLIK_SERVER
```

---

## SSL HTTPS (opsional, disarankan)

Install Certbot jika punya domain:

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d retensi.klinikkolbu.com
```

Update `.env`:

```env
APP_URL=https://retensi.klinikkolbu.com
SESSION_SECURE_COOKIE=true
```

---

## Update aplikasi (setelah ada perubahan code)

```bash
cd /var/www/retensi
git pull   # jika pakai git
sudo bash deploy/oracle/deploy-app.sh
```

---

## Cron retensi otomatis

Sudah diset oleh `setup-server.sh`:

```
* * * * * cd /var/www/retensi && php artisan schedule:run
```

Cek manual:

```bash
php artisan retensi:process
```

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Situs tidak bisa dibuka | Cek Security List port 80, `sudo systemctl status nginx` |
| 500 Error | `tail -f storage/logs/laravel.log` |
| Permission denied | `sudo chown -R www-data:www-data storage bootstrap/cache` |
| Database error | Cek `.env` DB_* dan `sudo systemctl status mysql` |
| Asset/CSS hilang | Jalankan ulang `npm run build` |

---

## Ringkasan arsitektur

```
Internet → Oracle VM (Ubuntu)
              ├── Nginx → PHP 8.3-FPM → Laravel Retensi
              ├── MySQL (retensirm)
              └── Cron (retensi:process harian)
```

---

*Klinik Kolbu — Retensi RM*
