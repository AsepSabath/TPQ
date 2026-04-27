# Sistem Manajemen Santri TPQ

Aplikasi web Laravel untuk membantu kepala madrasah dan tim operasional mengelola:

- Data santri dan biodata wali
- Absensi harian
- Tagihan dan pembayaran SPP
- Arus kas masuk/keluar
- Dashboard pimpinan dan early warning santri berisiko
- Audit log perubahan data
- Integrasi notifikasi WhatsApp (siap konfigurasi)

## Teknologi

- Laravel 13
- Breeze (auth + Blade)
- Spatie Permission (role dan permission)
- Tailwind CSS (mobile-first)

## Role Sistem

- kepala_madrasah
- admin_tu
- wali_kelas
- guru

## Akun Awal Seeder

- Admin TU
	- Email: admin@tpq.local
	- Password: Admin12345!
- Kepala Madrasah
	- Email: kepala@tpq.local
	- Password: Kepala12345!

## Modul yang Sudah Tersedia

- Dashboard KPI pimpinan
- CRUD data santri
- CRUD absensi harian
- CRUD absensi per mata pelajaran
- CRUD data kelas
- CRUD mata pelajaran
- CRUD nilai santri
- CRUD pelanggaran dan pembinaan
- CRUD tagihan SPP
- Input pembayaran SPP + auto update status invoice
- Auto catat kas masuk saat pembayaran SPP
- CRUD transaksi kas manual
- Laporan keuangan dengan filter periode + export CSV
- Laporan absensi dengan filter periode + export CSV
- Laporan semester + export PDF
- Impor data santri dari Excel/CSV
- WA reminder otomatis (tunggakan SPP + absensi alpha)
- Perhitungan skor risiko santri (command + schedule harian)
- Audit log untuk aksi utama

## Setup Lokal

1. Install dependensi:

```bash
composer install
npm install
```

2. Siapkan env dan app key:

```bash
copy .env.example .env
php artisan key:generate
```

3. Migrasi dan seed:

```bash
php artisan migrate:fresh --seed
```

4. Build assets:

```bash
npm run build
```

5. Jalankan aplikasi:

```bash
php artisan serve
```

## Deployment

Kalau ingin upload ke GitHub dan deploy ke VPS Ubuntu 24.04, lihat panduan lengkap di [DEPLOYMENT.md](DEPLOYMENT.md).

## Konfigurasi WhatsApp

Isi variabel berikut di file .env:

- WA_ENABLED=true
- WA_ENDPOINT=<url api gateway wa>
- WA_TOKEN=<token api>

Jika WA_ENABLED=false, notifikasi tetap masuk queue tabel wa_notifications tetapi tidak dikirim ke provider.

## Command Penting

- Hitung risk score manual:

```bash
php artisan app:calculate-risk-scores
```

Command ini juga dijadwalkan otomatis setiap hari jam 01:00.

- Kirim WA reminder manual:

```bash
php artisan app:send-wa-reminders
```

Command ini dijadwalkan otomatis setiap hari jam 06:00.
