<div align="center">

# 🛒 StockKu

### Web Aplikasi Penjualan (POS) & Absensi Karyawan

Aplikasi kasir modern untuk toko ritel: transaksi cepat tanpa reload, pembayaran **Tunai & QRIS**, mode **offline** yang tersinkron otomatis, plus manajemen inventaris, absensi karyawan, dan laporan lengkap.

[![PHP](https://img.shields.io/badge/PHP-%5E8.2-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-4.4-FB70A9?logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-3-06B6D4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.4-8BC0D0?logo=alpine.js&logoColor=white)](https://alpinejs.dev)

</div>

---

## 📑 Daftar Isi

- [✨ Fitur Utama](#-fitur-utama)
- [🛠️ Teknologi](#️-teknologi)
- [👤 Akun Demo](#-akun-demo)
- [🚀 Instalasi](#-instalasi)
- [📦 Struktur Direktori](#-struktur-direktori)
- [🌐 Catatan Deploy](#-catatan-deploy)

---

## ✨ Fitur Utama

### 🧾 Point of Sale (Kasir)
- **Kasir super cepat** berbasis Livewire — tambah barang, ubah qty, diskon, dan selesaikan transaksi tanpa reload halaman
- **Pembayaran Tunai & QRIS** (kode QRIS statis, tanpa perlu payment gateway)
- **Mode offline**: transaksi tersimpan di perangkat saat internet mati dan **sinkron otomatis** saat koneksi kembali
- **Keranjang persisten** — isi keranjang tidak hilang walau halaman di-refresh
- **Struk thermal 58mm/80mm** yang dioptimalkan untuk printer kasir
- Konfirmasi transaksi dengan **modal kustom** yang konsisten (bukan dialog bawaan browser)

### 📦 Manajemen Inventaris
- Kategori, Produk, dan Supplier dengan mutasi stok lengkap (Stok Masuk, Keluar, Retur)
- **Peringatan stok menipis** otomatis saat mencapai batas minimum
- Retur penjualan yang mengembalikan stok secara otomatis

### 👥 Karyawan & Absensi
- **Multi-role**: Admin, Manager, Kasir, Karyawan (Spatie Permission)
- Clock-In / Clock-Out harian, pengajuan **Izin / Sakit / Cuti** dengan alur persetujuan
- Rekap absensi bulanan

### 📊 Laporan
- Penjualan, Laba Rugi, Mutasi Stok, dan Rekap Absensi
- Ekspor ke **PDF** (DomPDF) & **Excel** (OpenSpout)

---

## 🛠️ Teknologi

| Lapisan | Teknologi |
|---|---|
| **Backend** | Laravel 12 · PHP 8.2+ · Livewire 4.4 |
| **Database** | MySQL |
| **Frontend** | Blade · Tailwind CSS 3 · Alpine.js · Vite |
| **Autentikasi & Otorisasi** | Laravel Breeze · Spatie Laravel Permission |
| **Ekspor Dokumen** | Barryvdh/Laravel-DomPDF · OpenSpout |
| **Offline & PWA** | IndexedDB · LocalStorage · Workbox (Service Worker) |

---

## 👤 Akun Demo

Seeder tersedia dengan data awal lengkap (termasuk riwayat transaksi 1 bulan). Akun yang dapat digunakan:

| Role | Email | Password | Hak Akses Utama |
|------|-------|----------|-----------------|
| 🛡️ **Admin** | `admin@stokcku.com` | `password` | Akses penuh ke semua modul, master data, laporan, dan pengaturan |
| 📊 **Manager** | `manager@stokcku.com` | `password` | Laporan & dashboard analitik (read-only, tanpa POS) |
| 💵 **Kasir** | `kasir1@stokcku.com` | `password` | Modul POS (Penjualan) & absensi pribadi |
| 🕒 **Karyawan** | `karyawan@stokcku.com` | `password` | Absensi harian & pengajuan izin/cuti |

---

## 🚀 Instalasi

### Prasyarat

- **PHP** ≥ 8.2 (dengan ekstensi `pdo_mysql`, `mbstring`, `xml`, `zip`, `gd`)
- **Composer**
- **Node.js** ≥ 18 & **npm**
- **MySQL** server

### Langkah

```bash
# 1. Install dependency
composer install
npm install

# 2. Siapkan environment
cp .env.example .env
php artisan key:generate

# 3. Atur koneksi database di .env, lalu jalankan migration + seeder
php artisan migrate:fresh --seed
# (Seeder butuh beberapa saat karena menghasilkan riwayat transaksi & mutasi stok 1 bulan)

# 4. Konfigurasi QRIS (opsional — untuk pembayaran QRIS statis)
# Isi di .env:
QRIS_STATIC_CODE=00020101021126620012ID.CO.QRIS.WWW-0111TESTQRIS00001
QRIS_HOLDER="Nama Toko Anda"

# 5. Kompilasi asset frontend
npm run build
# atau untuk development: npm run dev

# 6. Jalankan server
php artisan serve
```

Buka aplikasi di browser: **http://localhost:8000**

> 💡 Untuk menjalankan di jaringan LAN (akses dari perangkat kasir lain), gunakan:
> `php artisan serve --host=0.0.0.0 --port=8000`

---

## 📦 Struktur Direktori

```
app/
├── Http/
│   ├── Controllers/     # Controller per modul (Penjualan, Absensi, Laporan, ...)
│   ├── Middleware/      # Termasuk PreventStaleCache (anti-cache halaman web)
│   └── Requests/        # Form Request Validation
├── Livewire/
│   └── PosTerminal.php  # Komponen kasir (online + offline)
├── Services/            # Business logic layer agar controller ramping
└── Models/              # Eloquent Models
resources/
├── views/               # Blade + Tailwind + Alpine
└── js/
    ├── pwa/             # offlinePos.js (POS offline) & offline.js (antrian sinkron)
    └── confirm.js       # Modal konfirmasi kustom global
routes/web.php           # Definisi route
```

---

## 🌐 Catatan Deploy

- **Production**: gunakan web server (Nginx/Apache) dengan PHP-FPM, arahkan document root ke `public/`.
- **PWA / Service Worker**: fitur *installable app* dan *offline cache* hanya aktif pada koneksi **HTTPS** (atau `localhost`). Di jaringan HTTP LAN, aplikasi tetap berjalan normal — POS offline tetap berfungsi via LocalStorage/IndexedDB.
- **Keamanan**: pendaftaran publik dinonaktifkan (akun dibuat oleh Admin), login & lupa-password dibatasi `throttle` untuk mencegah brute-force, dan saat produksi pastikan `APP_DEBUG=false` di `.env`.
- **Email**: `MAIL_MAILER=log` (default) hanya menulis email ke file log — set konfigurasi SMTP asli agar *reset password* benar-benar terkirim.

### Mengelola Server via Systemd

File unit systemd sudah disediakan di `deploy/`:

```bash
sudo cp deploy/stockku.service deploy/stockku-backup.service deploy/stockku-backup.timer /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now stockku.service stockku-backup.timer
```

- `stockku.service` — menjalankan server (`php artisan serve --host=0.0.0.0 --port=8000`), restart otomatis jika crash, aktif saat boot.
- `stockku-backup.timer` + `stockku-backup.service` — backup database otomatis tiap pukul 02:30.

### HTTPS di Jaringan LAN

Aplikasi dijalankan di belakang **nginx** sebagai reverse proxy dengan sertifikat **self-signed** (CA internal) — port 8000 tidak lagi terbuka ke publik, hanya `443`.

Setup (sudah diterapkan di server):

```bash
# 1. Sertifikat & CA (CN = IP server, mis. 10.10.10.21)
mkdir -p /etc/ssl/stockku
openssl genrsa -out /etc/ssl/stockku/ca.key 2048
openssl req -x509 -new -nodes -key ca.key -sha256 -days 3650 \
  -subj "/CN=StockKu CA/O=StockKu/C=ID" -out /etc/ssl/stockku/stockku-ca.crt
# buat server.key + server.crt dengan SAN berisi IP server (lihat deploy/stockku-nginx.conf)

# 2. Konfigurasi nginx (lihat deploy/stockku-nginx.conf)
cp deploy/stockku-nginx.conf /etc/nginx/sites-available/stockku
ln -sf /etc/nginx/sites-available/stockku /etc/nginx/sites-enabled/stockku
nginx -t && systemctl restart nginx

# 3. HTTP -> HTTPS otomatis (sudah ada di konfigurasi nginx)
```

**Mempercayai CA di perangkat klien** (PC kasir, HP):

1. Salin `stockku-ca.crt` ke perangkat.
2. **Windows**: buka `stockku-ca.crt` → *Install Certificate* → *Local Machine* → *Trusted Root Certification Authorities*.
3. **Android**: Pengaturan → Keamanan → Instal sertifikat CA → pilih `stockku-ca.crt`.
4. **iOS**: instal profil `stockku-ca.crt`, lalu aktifkan *Certificate Trust Settings* → *Enable Full Trust*.

Tanpa mempercayai CA, browser tetap bisa dipaksa lanjut (peringatan "Not Secure"), tetapi **PWA / service worker tidak aktif**. Catatan: sertifikat berlaku 825 hari; regenerasi dengan langkah yang sama.

### Backup Database

Jalankan manual kapan saja:

```bash
./scripts/backup.sh
```

Dump disimpan di `storage/backups/stock-<timestamp>.sql.gz` dan file lebih dari 14 hari dihapus otomatis. Kredensial DB dibaca dari `.env` (tidak di-hardcode).

---

<div align="center">

Dibangun dengan ❤️ untuk efisiensi bisnis Anda.

</div>