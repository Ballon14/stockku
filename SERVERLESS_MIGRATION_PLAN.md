# Panduan Migrasi Serverless & Cloudflare D1 untuk StockKu

Dokumen ini merangkum analisis, tantangan, dan strategi jika aplikasi **StockKu** (Laravel 12, Livewire, MySQL) ingin dimigrasikan ke arsitektur *Serverless*, khususnya menggunakan database *Serverless* seperti **Cloudflare D1**.

## 1. Analisis Arsitektur Saat Ini vs Serverless

**Saat Ini:**
- **Compute:** Server tradisional / VPS (PHP-FPM, Nginx/Apache) yang menyala 24/7.
- **Database:** MySQL (Stateful, persistent TCP connection).
- **File System:** Local storage (dapat dimodifikasi dan persisten).
- **State/Session:** File-based atau database-based.

**Target (Serverless):**
- **Compute:** Ephemeral (hidup sesaat hanya saat ada request masuk), stateless, scale ke 0 jika tidak ada trafik.
- **Database:** Dioptimalkan untuk koneksi cepat (seringkali via HTTP API) karena environment serverless tidak bisa menahan koneksi TCP panjang.
- **File System:** Read-only (biasanya hanya bisa menulis sementara di `/tmp`).
- **State/Session:** Harus terdistribusi (misal: Redis/Memcached).

---

## 2. Tantangan: Laravel & Cloudflare Stack (Workers + D1)

Cloudflare Workers (dan Pages) berjalan di atas mesin **V8 Isolate (JavaScript/TypeScript)**, **bukan mesin PHP native**. 

Meskipun Cloudflare D1 (Serverless SQLite) sangat cepat dan berada di *edge*, ekosistem PHP/Laravel tidak dirancang secara native untuk berjalan di Cloudflare Workers. 
- Menjalankan PHP di Cloudflare Workers memerlukan kompilasi WebAssembly (WASM) yang saat ini praktiknya masih sangat **eksperimental** dan tidak stabil untuk production.
- Koneksi PHP ke Cloudflare D1 harus melalui HTTP API Cloudflare, yang berarti `PDO` standar SQLite tidak bisa langsung digunakan tanpa *custom driver*.

---

## 3. Opsi Jalur Migrasi Terbaik

Berdasarkan teknologi StockKu saat ini, jika Anda ingin beralih ke Serverless, berikut adalah 2 opsi paling realistis:

### Opsi A: Tetap Menggunakan Laravel + Serverless DB Lainnya (Rekomendasi)
Jika Anda ingin mempertahankan kode yang ada (Laravel 12 & Livewire), hindari Cloudflare Workers untuk *compute*. Gunakan platform serverless yang mendukung PHP native atau container.

*   **Compute (Pilih salah satu):**
    *   **Google Cloud Run:** Menjalankan Docker container Laravel (via FrankenPHP/Swoole). Mendukung scale ke 0.
    *   **AWS Lambda (via Bref atau Laravel Vapor):** Menjalankan fungsi PHP secara native di Lambda.
*   **Database (Pengganti Cloudflare D1):**
    *   **TiDB Serverless:** Paling direkomendasikan karena **MySQL-compatible**. Migrasi dari MySQL Anda saat ini (lokal) akan sangat mulus (nyaris *zero code change*).
    *   **Turso (Serverless SQLite):** Sangat mirip dengan konsep Cloudflare D1 dan memiliki driver resmi untuk PHP/Laravel.
    *   **Neon:** Serverless PostgreSQL.
*   **Storage (File/Gambar):** Pindah dari `local` ke **Cloudflare R2** (S3 compatible) yang biaya bandwidth-nya gratis.
*   **Session/Cache:** **Upstash** (Serverless Redis via HTTP).

### Opsi B: Full Rewrite ke Ekosistem Cloudflare (JS/TS)
Jika Anda bersikeras ingin menggunakan Cloudflare Workers dan Cloudflare D1 sepenuhnya untuk mendapatkan latensi edge terendah dan ekosistem Cloudflare native.

*   **Compute:** Cloudflare Pages / Workers.
*   **Framework Backend/Frontend:** Next.js, Nuxt, Remix, atau Hono.js. (Komponen Livewire dan Blade harus ditulis ulang ke React/Vue).
*   **Database:** **Cloudflare D1** (diakses menggunakan Drizzle ORM atau Prisma).
*   **Storage:** **Cloudflare R2**.
*   **Session/KV:** **Cloudflare KV**.
*   **Dampak:** Memerlukan penulisan ulang (*rewrite*) 100% kode codebase.

---

## 4. Penyesuaian Fitur Khusus StockKu untuk Serverless (Opsi A)

Jika Anda tetap menggunakan Laravel di Serverless (seperti AWS Lambda/Cloud Run), perhatikan *business logic* StockKu yang terdampak:

1.  **File System & Uploads**
    *   **Masalah:** Lingkungan serverless bersifat *read-only*.
    *   **Solusi:** Ubah environment `FILESYSTEM_DISK` ke `s3` (hubungkan ke Cloudflare R2).

2.  **PDF Generation (DOMPDF) & Excel Export (Spout)**
    *   **Masalah:** Memerlukan akses tulis untuk *temporary files*. Selain itu, AWS API Gateway memiliki batas *timeout* 30 detik; laporan besar bisa gagal.
    *   **Solusi:** Konfigurasi DOMPDF dan Spout untuk menulis ke direktori `/tmp` (satu-satunya tempat yang *writable*). Untuk laporan besar, pertimbangkan menggunakan **Queues (AWS SQS)** dan mengirim hasilnya via email.

3.  **Livewire State & Temporary Uploads**
    *   **Masalah:** Livewire secara default menyimpan status dan file unggahan sementara di lokal (`storage/app/livewire-tmp`).
    *   **Solusi:** Konfigurasi Livewire S3 temporary upload (`livewire.temporary_file_upload.disk = 's3'`).

4.  **Database Transactions (Data Integrity)**
    *   **Kritis:** Sesuai `AGENTS.md`, fitur POS dan Inventory (`SaleService::createSale()`, `StockService`) sangat bergantung pada `DB::transaction()` dan *row locking* (`lockForUpdate()`).
    *   **Solusi:** D1 atau Turso (SQLite via HTTP API) **tidak mendukung** *pessimistic row locking* yang sama kuatnya dengan MySQL/InnoDB. Oleh karena itu, menggunakan **TiDB Serverless** adalah pilihan paling aman untuk memastikan integritas data (Stock Guards) tidak tembus oleh *race conditions*.

5.  **PWA & Offline Sync**
    *   **Dampak:** Tidak ada dampak negatif. Fitur *Offline Sync PWA* akan mengirim HTTP request ke backend Serverless persis seperti server tradisional.

---

## 5. Kesimpulan

Untuk mempertahankan basis kode **StockKu (Laravel 12)**:
1. **Jangan gunakan Cloudflare D1/Workers**, karena kompatibilitas native PHP di Workers belum layak untuk production, dan D1 kurang cocok dengan sistem *row-locking* (integritas stok) yang sudah Anda bangun.
2. **Gunakan Google Cloud Run atau AWS Lambda (Bref)** sebagai Compute Serverless.
3. **Gunakan TiDB Serverless** sebagai Database Serverless (karena kompatibel dengan MySQL dan mendukung `DB::transaction()` serta `lockForUpdate()`).
4. **Gunakan Cloudflare R2** (S3-compatible) untuk menyimpan aset statis, invoice PDF, dan bukti transaksi.
