# Instalasi di VPS Ubuntu 24.04 — `https://tryoutnakes.bynama.id`

Panduan ini lengkap dari VPS kosong sampai situs online dengan HTTPS, email Brevo, antrean, cron, backup, dan optimasi. Ditulis untuk VPS **menengah (2 vCPU / 4 GB RAM)**; tabel tuning untuk ukuran lain ada di bagian 13.

> Ganti nilai berikut sesuai milik Anda:
> - `203.0.113.10` → IP publik VPS
> - `deploy` → nama user Linux untuk aplikasi
> - `PASSWORD_DB_KUAT` → kata sandi database
> - `admin@bynama.id` → email admin pertama

Susunan server:

```
Internet ─▶ Nginx (HTTPS, gzip, cache statis) ─▶ PHP 8.3-FPM + OPcache ─▶ Laravel 12 + Livewire 3
                                                     │                ├─▶ MySQL 8 (data)
                                                     │                └─▶ Redis (cache, sesi, antrean)
                                Supervisor ─▶ queue:work (email Brevo)    Cron ─▶ schedule:run (tiap menit)
```

---

## 1. DNS subdomain

Di panel DNS domain **bynama.id**, tambahkan:

| Tipe | Nama          | Nilai          | TTL  |
|------|---------------|----------------|------|
| A    | `tryoutnakes` | `203.0.113.10` | Auto |
| AAAA | `tryoutnakes` | IPv6 VPS (opsional) | Auto |

Cek dari komputer Anda (tunggu propagasi 1–30 menit):

```bash
dig +short tryoutnakes.bynama.id
```

> **Pakai Cloudflare?** Untuk tahap pertama set awan **abu-abu (DNS only)** sampai sertifikat Let's Encrypt terbit. Setelah itu boleh diaktifkan (oranye) dengan SSL mode **Full (strict)**. Lihat bagian 15.

---

## 2. Persiapan server & keamanan dasar

Login sebagai root:

```bash
ssh root@203.0.113.10
apt update && apt -y upgrade
timedatectl set-timezone Asia/Makassar        # sesuaikan (Asia/Jakarta, dst.)
apt -y install curl git unzip ufw fail2ban software-properties-common acl
```

Buat user khusus aplikasi (jangan jalankan aplikasi sebagai root):

```bash
adduser deploy
usermod -aG sudo,www-data deploy
rsync --archive --chown=deploy:deploy ~/.ssh /home/deploy   # salin kunci SSH (jika ada)
```

Firewall & proteksi brute-force:

```bash
ufw allow OpenSSH
ufw allow 'Nginx Full'   # baru tersedia setelah Nginx terpasang; jalankan lagi di langkah 4 jika perlu
ufw --force enable
systemctl enable --now fail2ban
```

Perkuat SSH (setelah memastikan login dengan kunci SSH sebagai `deploy` berhasil):

```bash
sed -i 's/^#\?PermitRootLogin.*/PermitRootLogin no/' /etc/ssh/sshd_config
sed -i 's/^#\?PasswordAuthentication.*/PasswordAuthentication no/' /etc/ssh/sshd_config
systemctl restart ssh
```

Update keamanan otomatis:

```bash
apt -y install unattended-upgrades && dpkg-reconfigure -plow unattended-upgrades
```

**Swap 2 GB** (penting untuk VPS ≤ 4 GB agar `npm run build`/composer tidak kehabisan RAM):

```bash
fallocate -l 2G /swapfile && chmod 600 /swapfile && mkswap /swapfile && swapon /swapfile
echo '/swapfile none swap sw 0 0' >> /etc/fstab
echo 'vm.swappiness=10' > /etc/sysctl.d/99-swap.conf && sysctl --system
```

---

## 3. PHP 8.3 + ekstensi

Ubuntu 24.04 sudah membawa PHP 8.3:

```bash
apt -y install php8.3-fpm php8.3-cli php8.3-mysql php8.3-redis php8.3-mbstring php8.3-xml \
  php8.3-curl php8.3-zip php8.3-gd php8.3-intl php8.3-bcmath php8.3-opcache php8.3-readline
php -v
php -r 'var_dump(gd_info()["WebP Support"]);'   # harus bool(true) — gambar dikonversi ke WebP
```

Composer:

```bash
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
composer --version
```

---

## 4. Nginx, MySQL, Redis, Supervisor, Node.js

```bash
apt -y install nginx mysql-server redis-server supervisor
ufw allow 'Nginx Full'

# Node.js 20 LTS (hanya untuk build aset Tailwind/Vite)
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt -y install nodejs
node -v && npm -v
```

### 4.1 Amankan & siapkan MySQL

```bash
mysql_secure_installation      # jawab: Y untuk semua (hapus user anonim, larang root remote, hapus db test)
mysql
```

```sql
CREATE DATABASE tryoutnakes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'tryoutnakes'@'localhost' IDENTIFIED BY 'PASSWORD_DB_KUAT';
GRANT ALL PRIVILEGES ON tryoutnakes.* TO 'tryoutnakes'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Tuning MySQL (salin file contoh dari proyek, langkah 5):

```bash
cp /var/www/tryoutnakes/docs/server/mysql-tuning.cnf /etc/mysql/mysql.conf.d/zz-tryoutnakes.cnf
systemctl restart mysql
```

### 4.2 Redis

```bash
sed -i 's/^supervised .*/supervised systemd/' /etc/redis/redis.conf
sed -i 's/^# maxmemory <bytes>/maxmemory 256mb/' /etc/redis/redis.conf
sed -i 's/^# maxmemory-policy .*/maxmemory-policy allkeys-lru/' /etc/redis/redis.conf
systemctl restart redis-server && redis-cli ping     # PONG
```

> Redis hanya mendengarkan `127.0.0.1` secara default — biarkan begitu.

---

## 5. Unggah kode aplikasi

**Opsi A — lewat Git (disarankan):** buat repository **privat** (GitHub/GitLab), push isi zip proyek, lalu di VPS:

```bash
sudo mkdir -p /var/www && sudo chown deploy:www-data /var/www
su - deploy
cd /var/www
git clone git@github.com:AKUN/tryoutnakes.git tryoutnakes
```

**Opsi B — unggah zip:** dari komputer Anda:

```bash
scp tryoutnakes.zip deploy@203.0.113.10:/var/www/
ssh deploy@203.0.113.10
cd /var/www && unzip tryoutnakes.zip -d tryoutnakes
```

Izin folder:

```bash
cd /var/www/tryoutnakes
sudo chown -R deploy:www-data .
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type f -exec chmod 644 {} \;
chmod +x deploy.sh artisan
sudo chmod -R ug+rwx storage bootstrap/cache
sudo setfacl -R -m g:www-data:rwx -m d:g:www-data:rwx storage bootstrap/cache
```

---

## 6. Konfigurasi `.env` produksi

```bash
cp .env.example .env
nano .env
```

Isi minimal berikut (sisanya biarkan):

```dotenv
APP_NAME="NakesPrep"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tryoutnakes.bynama.id
APP_TIMEZONE=Asia/Makassar

LOG_STACK=daily
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tryoutnakes
DB_USERNAME=tryoutnakes
DB_PASSWORD=PASSWORD_DB_KUAT

CACHE_STORE=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=240
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis

MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=xxxxxx@smtp-brevo.com     # "Login" di Brevo → SMTP & API
MAIL_PASSWORD=xsmtpsib-xxxxxxxxxxxxxxxx   # "SMTP key" (bukan password akun)
MAIL_FROM_ADDRESS="noreply@bynama.id"     # harus domain/pengirim yang sudah diverifikasi di Brevo
MAIL_FROM_NAME="${APP_NAME}"

ADMIN_NAME="Administrator"
ADMIN_EMAIL=admin@bynama.id
ADMIN_PASSWORD=GantiDenganPasswordKuat!

INDEXNOW_KEY=                              # isi di langkah SEO (opsional)
```

Amankan file:

```bash
chmod 640 .env && sudo chown deploy:www-data .env
```

---

## 7. Instal dependensi, migrasi, dan build

```bash
cd /var/www/tryoutnakes
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force          # kategori, admin, FAQ, contoh metode bayar (nonaktif)
php artisan storage:link
npm ci || npm install
npm run build
php artisan optimize
php artisan view:cache
```

> **Data contoh (opsional)** — untuk mencoba semua fitur sebelum mengisi soal asli:
> `php artisan db:seed --class=DemoSeeder --force` (menambah ±95 soal contoh, 28 tryout, 7 paket, dan akun `peserta@demo.test` / `password123`). Hapus data contoh sebelum go-live dengan `php artisan migrate:fresh --seed --force` (**menghapus seluruh data**).

Jika lupa password admin kapan pun:

```bash
php artisan app:create-admin admin@bynama.id --name="Administrator"
```

---

## 8. PHP-FPM pool & OPcache

```bash
sudo cp docs/server/php-fpm-pool.conf /etc/php/8.3/fpm/pool.d/tryoutnakes.conf
sudo cp docs/server/opcache.ini /etc/php/8.3/mods-available/opcache-tuning.ini
sudo phpenmod opcache-tuning
sudo sed -i 's/^expose_php = On/expose_php = Off/' /etc/php/8.3/fpm/php.ini
sudo php-fpm8.3 -t && sudo systemctl restart php8.3-fpm
ls -l /run/php/php8.3-fpm-tryoutnakes.sock
```

> `opcache.validate_timestamps=0` membuat PHP sangat cepat, tetapi **setiap kali kode berubah wajib** `sudo systemctl reload php8.3-fpm` (sudah otomatis di `deploy.sh`).

Izinkan user `deploy` me-reload PHP-FPM tanpa password (dipakai `deploy.sh`):

```bash
echo 'deploy ALL=NOPASSWD: /usr/bin/systemctl reload php8.3-fpm' | sudo tee /etc/sudoers.d/deploy-fpm
sudo chmod 440 /etc/sudoers.d/deploy-fpm
```

---

## 9. Nginx + HTTPS (Let's Encrypt)

Pasang dulu konfigurasi HTTP sementara agar Certbot bisa memverifikasi domain:

```bash
sudo tee /etc/nginx/sites-available/tryoutnakes >/dev/null <<'EOF'
server {
    listen 80;
    server_name tryoutnakes.bynama.id;
    root /var/www/tryoutnakes/public;
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ { include snippets/fastcgi-php.conf; fastcgi_pass unix:/run/php/php8.3-fpm-tryoutnakes.sock; }
}
EOF
sudo ln -s /etc/nginx/sites-available/tryoutnakes /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

Terbitkan sertifikat:

```bash
sudo apt -y install certbot python3-certbot-nginx
sudo certbot --nginx -d tryoutnakes.bynama.id --agree-tos -m admin@bynama.id --redirect --no-eff-email
```

Ganti dengan konfigurasi final yang sudah dioptimasi (HTTP→HTTPS, HTTP/2, HSTS, gzip, cache aset):

```bash
sudo cp /var/www/tryoutnakes/docs/server/nginx-tryoutnakes.conf /etc/nginx/sites-available/tryoutnakes
sudo nginx -t && sudo systemctl reload nginx
```

Tambahan di `/etc/nginx/nginx.conf` (blok `http { ... }`):

```nginx
server_tokens off;
client_body_buffer_size 32k;
keepalive_timeout 30;
```

Cek perpanjangan otomatis sertifikat:

```bash
sudo certbot renew --dry-run
systemctl list-timers | grep certbot
```

Buka **https://tryoutnakes.bynama.id** — selesai! Cek nilai SSL di https://www.ssllabs.com/ssltest/ (target A/A+).

---

## 10. Antrean email (Supervisor) & penjadwal (Cron)

Semua email (pesanan, verifikasi, hasil tryout, broadcast) dikirim lewat antrean agar halaman tetap cepat.

```bash
sudo cp /var/www/tryoutnakes/docs/server/supervisor-tryoutnakes.conf /etc/supervisor/conf.d/tryoutnakes.conf
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl status          # tryoutnakes-queue_00 RUNNING
```

Penjadwal Laravel (mengumpulkan ujian yang waktunya habis, menandai pesanan kedaluwarsa, menyegarkan sitemap, IndexNow):

```bash
sudo crontab -u deploy -e
```

Tambahkan baris:

```cron
* * * * * cd /var/www/tryoutnakes && php artisan schedule:run >> /dev/null 2>&1
```

Cek: `php artisan schedule:list`.

---

## 11. Email Brevo (gratis 300 email/hari)

1. Daftar di https://www.brevo.com → **Senders, Domains & Dedicated IPs → Domains → Add a domain** → `bynama.id`.
2. Brevo menampilkan record DNS. Tambahkan di DNS **bynama.id**:

   | Tipe | Nama | Nilai |
   |------|------|-------|
   | TXT  | `@`  | `brevo-code:xxxxxxxx` (kode verifikasi dari Brevo) |
   | TXT  | `@`  | `v=spf1 include:spf.brevo.com ~all` *(gabungkan dengan SPF yang sudah ada — hanya boleh 1 record SPF)* |
   | TXT/CNAME | `mail._domainkey` (atau sesuai Brevo) | nilai DKIM dari Brevo |
   | TXT  | `_dmarc` | `v=DMARC1; p=none; rua=mailto:admin@bynama.id` |

3. Tunggu status **Authenticated**, lalu buat sender `noreply@bynama.id`.
4. **SMTP & API → SMTP** → salin *Login* dan buat *SMTP key* → isi ke `.env` (langkah 6).
5. Terapkan perubahan & tes:

```bash
php artisan optimize && php artisan queue:restart
```

Lalu buka **Admin → Pengaturan → Email → Kirim email tes**.

> **Kuota gratis 300 email/hari.** Satu pembelian ≈ 3–4 email (pesanan dibuat, bukti diterima, disetujui, hasil tryout). Jika mendekati batas: matikan "Kirim salinan notifikasi ke email" di Pengaturan (notifikasi di aplikasi tetap jalan), atau naikkan paket Brevo. Hindari broadcast email ke ribuan peserta pada paket gratis.

---

## 12. Backup otomatis

```bash
sudo mkdir -p /var/backups/tryoutnakes && sudo chown deploy:deploy /var/backups/tryoutnakes
cat > /home/deploy/.my.cnf <<'EOF'
[mysqldump]
user=tryoutnakes
password=PASSWORD_DB_KUAT
EOF
chmod 600 /home/deploy/.my.cnf
crontab -e
```

```cron
# Backup DB setiap jam 02:30, file unggahan setiap Minggu, simpan 14 hari
30 2 * * * mysqldump --single-transaction --quick tryoutnakes | gzip > /var/backups/tryoutnakes/db-$(date +\%F).sql.gz
45 2 * * 0 tar -czf /var/backups/tryoutnakes/storage-$(date +\%F).tar.gz -C /var/www/tryoutnakes/storage app
0 3 * * * find /var/backups/tryoutnakes -type f -mtime +14 -delete
```

> Salin backup ke luar VPS (Google Drive dengan `rclone`, atau object storage) — backup di server yang sama tidak melindungi dari kerusakan VPS.

Restore: `gunzip < db-2026-10-24.sql.gz | mysql tryoutnakes`.

---

## 13. Tuning sesuai ukuran VPS

| RAM VPS | `pm.max_children` | `innodb_buffer_pool_size` | Redis `maxmemory` | OPcache memory |
|---------|-------------------|---------------------------|-------------------|----------------|
| 2 GB    | 12                | 512M                      | 128mb             | 128            |
| **4 GB**| **24**            | **1G**                    | **256mb**         | **192**        |
| 8 GB    | 48                | 3G                        | 512mb             | 256            |

Rumus kasar `pm.max_children` = (RAM untuk PHP) ÷ ~60 MB. Pantau dengan `htop` dan `sudo tail -f /var/log/php8.3-fpm-tryoutnakes.log` (jika muncul *"server reached pm.max_children"*, naikkan sedikit).

Kapasitas perkiraan 2 vCPU / 4 GB: ratusan peserta mengerjakan tryout bersamaan — navigasi soal berjalan di browser, server hanya menerima simpan jawaban ringan (JSON kecil, ±5–15 ms per permintaan).

---

## 14. Update aplikasi (deploy berikutnya)

```bash
ssh deploy@203.0.113.10
cd /var/www/tryoutnakes
./deploy.sh
```

Script melakukan: maintenance mode → `git pull` → composer → build aset → migrasi → cache ulang → restart worker & reload PHP-FPM → online kembali.

Jika memakai unggah zip: ekstrak di atas folder lama (**jangan timpa `.env` dan `storage/`**), lalu `./deploy.sh`.

---

## 15. (Opsional) Cloudflare di depan server

1. Setelah HTTPS jalan, aktifkan proxy (awan oranye) dan set **SSL/TLS → Full (strict)**.
2. Agar IP asli peserta terbaca (rate-limit & log), tambahkan di blok `server` Nginx:

```nginx
# Daftar terbaru: https://www.cloudflare.com/ips/
set_real_ip_from 173.245.48.0/20;
set_real_ip_from 103.21.244.0/22;
set_real_ip_from 103.22.200.0/22;
set_real_ip_from 103.31.4.0/22;
set_real_ip_from 141.101.64.0/18;
set_real_ip_from 108.162.192.0/18;
set_real_ip_from 190.93.240.0/20;
set_real_ip_from 188.114.96.0/20;
set_real_ip_from 197.234.240.0/22;
set_real_ip_from 198.41.128.0/17;
set_real_ip_from 162.158.0.0/15;
set_real_ip_from 104.16.0.0/13;
set_real_ip_from 104.24.0.0/14;
set_real_ip_from 172.64.0.0/13;
set_real_ip_from 131.0.72.0/22;
real_ip_header CF-Connecting-IP;
```

3. Jangan aktifkan *Rocket Loader* (mengganggu Livewire/Alpine). *Auto Minify* tidak perlu (aset sudah minified).

---

## 16. Pemeriksaan akhir (checklist go-live)

- [ ] `https://tryoutnakes.bynama.id` terbuka, gembok hijau, `http://` otomatis dialihkan.
- [ ] `APP_DEBUG=false`, `APP_ENV=production` (cek: buka URL acak → halaman 404 biasa, tanpa detail error).
- [ ] Login admin → **ganti password** di Profil.
- [ ] Admin → Pengaturan: nama brand, WhatsApp admin, batas verifikasi, kode Search Console.
- [ ] Admin → Metode Pembayaran: unggah **QRIS** (wajib) + aktifkan/ubah rekening contoh.
- [ ] Admin → Kategori & Harga: atur harga satuan per kategori.
- [ ] Buat/impor soal → buat tryout → isi soal → **Terbitkan** → buat paket.
- [ ] Uji alur lengkap dengan akun peserta: daftar → beli → unggah bukti → setujui → kerjakan → hasil.
- [ ] Email tes terkirim, `supervisorctl status` RUNNING, `php artisan schedule:list` tampil.
- [ ] `https://tryoutnakes.bynama.id/sitemap.xml` & `/robots.txt` tampil → daftarkan ke Search Console (lihat `docs/SEO.md`).
- [ ] Backup cron berjalan (cek `/var/backups/tryoutnakes` keesokan hari).

## 17. Pemecahan masalah

| Gejala | Solusi |
|---|---|
| 502 Bad Gateway | `sudo systemctl status php8.3-fpm`; pastikan nama socket di Nginx = `php8.3-fpm-tryoutnakes.sock`. |
| 500 setelah update | `tail -n 50 storage/logs/laravel-*.log`; jalankan `php artisan optimize:clear && php artisan optimize`; reload PHP-FPM. |
| Perubahan kode tidak terlihat | OPcache: `sudo systemctl reload php8.3-fpm`. |
| Gambar tidak muncul | `php artisan storage:link`; izin `storage/` (langkah 5). |
| Email tidak terkirim | `supervisorctl status`; `tail storage/logs/queue.log`; cek kredensial Brevo & domain terverifikasi; `php artisan queue:failed`. |
| Upload gagal "413" | Naikkan `client_max_body_size` Nginx & `upload_max_filesize` pool PHP. |
| Ujian tidak tertutup otomatis | Pastikan cron `schedule:run` aktif. |
| Halaman 419 "Page Expired" | Sesi kedaluwarsa / `APP_URL` tidak https; samakan `APP_URL` dan `SESSION_SECURE_COOKIE=true`. |
