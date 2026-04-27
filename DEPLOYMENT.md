# Deployment ke GitHub dan VPS Ubuntu 24.04

Panduan ini disesuaikan untuk proyek Laravel TPQ ini yang memakai Laravel 13, Vite, queue database, dan scheduler harian.

## 1. Upload ke GitHub

1. Pastikan file rahasia tidak ikut masuk repository. Minimal yang tidak boleh di-commit:
   - `.env`
   - `storage/`
   - file credential API, token, atau backup database

2. Inisialisasi git kalau belum ada:

```bash
git init
git add .
git commit -m "Initial commit"
```

3. Buat repository baru di GitHub, lalu hubungkan remote:

```bash
git branch -M main
git remote add origin https://github.com/USERNAME/NAMA-REPO.git
git push -u origin main
```

4. Kalau repo sudah pernah dibuat, cukup update biasa:

```bash
git add .
git commit -m "Update project"
git push
```

## 2. Persiapan VPS Ubuntu 24.04

### Paket yang perlu dipasang

Minimal install:

- Nginx
- PHP 8.3 + ekstensi Laravel
- Composer
- Node.js untuk build asset, atau build dilakukan lokal lalu hasilnya di-upload
- Database: MariaDB/MySQL atau SQLite

Contoh paket umum:

```bash
sudo apt update
sudo apt install -y nginx git unzip curl software-properties-common
sudo apt install -y php8.3 php8.3-cli php8.3-fpm php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl php8.3-mysql
```

Kalau kamu tetap mau pakai SQLite, pastikan ekstensi `php8.3-sqlite3` terpasang.

### Composer dan Node.js

Pastikan Composer sudah terpasang. Untuk build asset di server, pasang Node.js versi LTS juga.

## 3. Deploy project ke server

1. Masuk ke folder aplikasi, misalnya:

```bash
cd /var/www
sudo git clone https://github.com/USERNAME/NAMA-REPO.git tpq
cd tpq
```

2. Buat file `.env` dari contoh:

```bash
cp .env.example .env
```

3. Isi konfigurasi produksi di `.env`:

```env
APP_NAME="Sistem Manajemen Santri TPQ"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tpq
DB_USERNAME=tpq_user
DB_PASSWORD=password_kuat

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
```

4. Install dependency backend:

```bash
composer install --no-dev --optimize-autoloader
```

5. Install dependency frontend dan build asset:

```bash
npm install
npm run build
```

Kalau kamu membangun asset di lokal, pastikan folder `public/build` ikut masuk ke server.

6. Generate app key kalau masih kosong:

```bash
php artisan key:generate
```

7. Jalankan migrasi dan seed bila diperlukan:

```bash
php artisan migrate --force
php artisan db:seed --force
```

Kalau ini server produksi baru, biasanya seeding awal boleh dijalankan sekali saja untuk akun default.

8. Buat symbolic link storage:

```bash
php artisan storage:link
```

9. Bersihkan dan cache konfigurasi:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 4. Nginx

Set document root ke folder `public`.

Contoh konfigurasi server block:

```nginx
server {
    listen 80;
    server_name domain-anda.com;
    root /var/www/tpq/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Setelah file disimpan, aktifkan site lalu reload Nginx.

## 5. Queue worker

Project ini memakai queue database, jadi worker harus jalan terus di server.

Contoh pakai Supervisor:

```ini
[program:tpq-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/tpq/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/tpq-worker.log
```

## 6. Scheduler harian

Karena ada command terjadwal di `routes/console.php`, tambahkan cron:

```bash
* * * * * cd /var/www/tpq && php artisan schedule:run >> /dev/null 2>&1
```

## 7. Alur update berikutnya

Kalau ada perubahan kode di lokal:

```bash
git add .
git commit -m "Perubahan fitur"
git push
```

Di server:

```bash
cd /var/www/tpq
git pull
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart php8.3-fpm
sudo systemctl reload nginx
```

## 8. Checklist cepat sebelum go live

- `APP_DEBUG=false`
- `APP_ENV=production`
- `APP_URL` sudah benar
- queue worker aktif
- cron scheduler aktif
- storage link sudah dibuat
- asset `public/build` sudah ada
- permission folder `storage` dan `bootstrap/cache` sudah bisa ditulis user web server
