# Panduan Fungsi Halaman (Toko Makmur)

Dokumen ini menjelaskan fungsi dari masing-masing halaman dan menu yang ada di dalam sistem Point of Sale (POS) & Manajemen Stok "Toko Makmur".

---

## 📊 1. Dashboard
Halaman utama yang muncul setelah login. Berfungsi sebagai pusat informasi ringkas (Executive Summary).
- **Fungsi Utama:**
  - Melihat total penjualan hari ini dan bulan ini.
  - Melihat grafik tren penjualan (7 hari, 30 hari, 12 bulan terakhir).
  - Melihat daftar produk terlaris bulan ini.
  - Memantau produk yang stoknya menipis.
  - Melihat ringkasan absensi karyawan (hadir, sakit, izin, dll).

---

## 🛒 2. Kasir (POS)
*(Akses: Admin & Kasir)*
Halaman tempat transaksi jual-beli ke pelanggan berlangsung secara real-time.
- **Fungsi Utama:**
  - Melakukan scan barcode atau pencarian nama barang.
  - Menambahkan barang ke dalam keranjang (cart).
  - Mengubah kuantitas (Qty) dan memberikan diskon (per-item atau global).
  - Memproses pembayaran tunai, transfer, atau QRIS.
  - Mendukung mode offline (transaksi tanpa internet akan disinkronisasi ketika online kembali).
  - Menghasilkan dan mencetak struk kasir otomatis.

---

## 📦 3. Master Data
*(Akses: Khusus Admin)*
Kelompok menu untuk mengatur data inti sistem.
- **Kategori:** Mengelompokkan produk (contoh: Minuman, Makanan Ringan, Kebutuhan Pokok).
- **Produk:** Mengelola daftar barang yang dijual, harga beli dasar, harga jual, SKU/Barcode, stok awal, dan batas minimal stok (agar muncul peringatan).
- **Supplier:** Menyimpan data pemasok (nama, kontak, alamat) untuk keperluan pembelian ulang barang (restock).
- **Karyawan:** Mendaftarkan akun karyawan baru, menentukan jabatan, peran (Role: admin, manager, kasir, karyawan), dan password akun mereka.
- **Shift Karyawan:** Menentukan jadwal jam kerja (masuk dan pulang) yang akan dijadikan acuan pada sistem absensi.

---

## 💼 4. Transaksi
Mengelola dan merekam pergerakan barang secara finansial.
- **Riwayat Penjualan:** *(Akses: Admin & Kasir)*
  - Melihat daftar semua struk/invoice yang telah diterbitkan.
  - Mencetak ulang struk lama.
- **Retur Penjualan:** *(Akses: Admin)*
  - Tempat menyetujui (approve) atau menolak pengembalian barang dari pelanggan, serta menentukan apakah barang rusak atau kembali ke stok normal.
- **Pembelian (Restock):** *(Akses: Admin)*
  - Menginput faktur/invoice pembelian barang dari supplier.
  - Stok produk akan otomatis bertambah saat pembelian dicatat.
  - Harga beli produk yang dimasukkan di sini akan mempengaruhi laporan HPP (Harga Pokok Penjualan).

---

## 📈 5. Stok
*(Akses: Khusus Admin)*
- **Kartu Stok:** 
  - Catatan audit menyeluruh tentang keluar masuknya satu barang spesifik (kapan terjual, kapan direstock, kapan diretur).
- **Stok Menipis:** 
  - Alarm yang menampilkan produk-produk yang jumlah stoknya berada di bawah batas minimum yang ditentukan di Master Produk.

---

## ⏱️ 6. Absensi
- **Clock In / Out:** *(Akses: Karyawan & Kasir)*
  - Halaman wajib untuk menekan tombol "Mulai Kerja" (Clock In) dan "Selesai Kerja" (Clock Out). 
  - *Catatan:* Jika karyawan belum Clock In, mereka tidak akan bisa masuk ke menu Kasir.
- **Riwayat Absensi:** *(Akses: Karyawan & Kasir)*
  - Melihat catatan kehadiran diri sendiri di hari-hari sebelumnya.
- **Rekap Absensi:** *(Akses: Admin)*
  - Melihat daftar kehadiran seluruh karyawan dalam perusahaan secara komprehensif.
- **Pengajuan Izin/Cuti:** 
  - Karyawan bisa mengajukan sakit/izin dari sini, dan Admin bisa melakukan approval.

---

## 📑 7. Laporan
*(Akses: Admin & Manager)*
- **Laporan Penjualan:** Filter penjualan berdasarkan tanggal atau kasir tertentu.
- **Laba Rugi:** Menghitung total pendapatan (Revenue), Harga Pokok Penjualan (HPP/COGS), diskon, dan retur, untuk mengetahui Laba Kotor (Gross Profit).
- **Mutasi Stok:** Laporan agregat pergerakan barang.
- **Laporan Absensi:** Ringkasan kedisiplinan dan kehadiran per karyawan.
- **Perubahan Harga:** Melacak produk mana saja yang mengalami kenaikan atau penurunan harga beli dari supplier dibandingkan riwayat sebelumnya, agar owner tidak rugi jika lupa menaikkan harga jual di sistem.
  - *Semua laporan dapat di-export ke dalam bentuk Excel maupun Cetak PDF.*

---

## ⚙️ 8. Sistem
*(Akses: Khusus Admin)*
- **Log Aktivitas (Activity Logs):**
  - Merekam setiap aksi penting yang dilakukan di dalam sistem. Jika ada stok yang tiba-tiba berubah atau transaksi yang dihapus, Admin bisa melihat siapa akun yang melakukannya dan kapan hal tersebut terjadi.
