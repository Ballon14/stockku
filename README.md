# StokCku - Web Aplikasi Penjualan & Absensi 🚀

StokCku adalah aplikasi web komprehensif yang dirancang untuk mengelola penjualan toko ritel sekaligus melacak absensi karyawan. Dibangun menggunakan teknologi modern untuk menjamin performa cepat, antarmuka yang indah, dan pengalaman pengguna yang mulus.

## ✨ Fitur Utama

- **Multi-Role Authentication**: Akses berbasis role (Admin, Manager, Kasir, Karyawan) menggunakan Spatie Permission.
- **Point of Sale (POS) Super Cepat**: Menggunakan Livewire untuk pengalaman kasir tanpa reload halaman.
- **Manajemen Inventaris**: Pengelolaan Kategori, Produk, Supplier, dan riwayat mutasi stok (Stok Masuk, Keluar, Retur).
- **Peringatan Stok Menipis**: Notifikasi otomatis ketika stok produk mencapai batas minimum.
- **Manajemen Karyawan & Absensi**: Fitur Clock-In/Clock-Out harian dan pengajuan Izin/Sakit/Cuti terintegrasi.
- **Retur Penjualan**: Sistem pengembalian barang dari pelanggan yang secara otomatis mengembalikan stok.
- **Laporan Lengkap**: Laporan Penjualan, Laba Rugi, Mutasi Stok, dan Rekap Absensi yang dapat diekspor ke PDF/Excel.
- **Struk Kasir Thermal**: Format cetak struk penjualan yang dioptimalkan untuk printer thermal (58mm/80mm).

## 🛠️ Teknologi yang Digunakan

- **Backend**: Laravel 11.x (PHP 8.2)
- **Database**: MySQL
- **Frontend & Styling**: Blade Templates, Tailwind CSS 4, AlpineJS
- **Interaktivitas (POS)**: Livewire 3
- **Autentikasi & Otorisasi**: Laravel Breeze & Spatie Laravel Permission
- **Ekspor Dokumen**: Barryvdh/Laravel-DomPDF (PDF) & OpenSpout (Excel)

## 👤 Role Pengguna & Akses Default

Aplikasi sudah dilengkapi dengan *Seeder* untuk data awal (termasuk dummy transaksi). Berikut adalah akun yang dapat digunakan:

| Role | Email | Password | Hak Akses Utama |
|------|-------|----------|-----------------|
| **Admin** | admin@stokcku.com | `password` | Akses penuh ke semua modul, master data, laporan, dan setting. |
| **Manager** | manager@stokcku.com | `password` | Laporan, dashboard analitik, read-only data (tanpa POS). |
| **Kasir** | kasir@stokcku.com | `password` | Modul POS (Penjualan), absensi pribadi. |
| **Karyawan** | karyawan@stokcku.com | `password` | Hanya absensi harian dan pengajuan izin/cuti. |

## 🚀 Cara Menjalankan Aplikasi

Aplikasi telah dikonfigurasi. Berikut adalah panduan singkat jika Anda ingin menjalankannya ulang dari awal:

1. **Jalankan Migration & Seeder**
   ```bash
   php artisan migrate:fresh --seed
   ```
   *(Catatan: Seeder akan memakan waktu beberapa saat karena menghasilkan riwayat transaksi & mutasi stok yang lengkap selama 1 bulan terakhir).*

2. **Kompilasi Asset Frontend (Tailwind)**
   ```bash
   npm run build
   # atau untuk mode development: npm run dev
   ```

3. **Jalankan Server Laravel**
   ```bash
   php artisan serve
   ```

4. Buka aplikasi di browser pada alamat: `http://localhost:8000`

## 📦 Struktur Direktori Utama

- `app/Http/Controllers/`: Menyimpan semua Controller (dipisahkan per modul).
- `app/Services/`: Berisi logika bisnis aplikasi (Business Logic Layer) agar controller tetap ramping.
- `app/Http/Requests/`: Berisi form request validation untuk memvalidasi input.
- `app/Livewire/`: Menyimpan komponen Livewire, khususnya `PosTerminal` untuk fitur kasir.
- `resources/views/`: Layout dan halaman frontend menggunakan kombinasi Blade + Tailwind + AlpineJS.

---
*Dibangun dengan ❤️ untuk efisiensi bisnis Anda.*
