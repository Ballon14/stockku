<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * Tampilkan halaman pengaturan database.
     */
    public function databaseIndex()
    {
        return view('settings.database');
    }

    /**
     * Jalankan proses ekspor/backup database ke file .sql (Pure PHP)
     */
    public function backupDatabase()
    {
        $database = env('DB_DATABASE');
        $filename = 'backup_' . $database . '_' . date('Y-m-d_H-i-s') . '.sql';
        $path = storage_path('app/private/' . $filename);

        // Pastikan direktori ada
        if (!File::exists(storage_path('app/private'))) {
            File::makeDirectory(storage_path('app/private'), 0755, true);
        }

        try {
            // Kita akan menggunakan Pure PHP untuk membackup database agar terhindar dari isu mysqldump TCP/IP Windows
            $tables = DB::select('SHOW TABLES');
            $tablesProperty = "Tables_in_{$database}";

            $sqlScript = "-- Database Backup\n";
            $sqlScript .= "-- Waktu: " . date('Y-m-d H:i:s') . "\n";
            $sqlScript .= "-- Database: {$database}\n\n";
            $sqlScript .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                // PDO mengembalikan objek dengan properti dinamis (nama db) atau bentuk array tergantung fetch mode
                $tableName = (array)$table;
                $tableName = array_values($tableName)[0];

                $sqlScript .= "--\n-- Struktur tabel untuk `{$tableName}`\n--\n";
                $sqlScript .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $createTableProperty = 'Create Table';
                $createTableStmt = $createTable[0]->$createTableProperty ?? ((array)$createTable[0])['Create Table'];
                $sqlScript .= $createTableStmt . ";\n\n";

                $sqlScript .= "--\n-- Data untuk tabel `{$tableName}`\n--\n";
                $rows = DB::table($tableName)->get();

                if ($rows->count() > 0) {
                    // Agar efisien, kita batch per 100 baris jika jumlahnya banyak
                    foreach ($rows->chunk(100) as $chunk) {
                        $inserts = [];
                        foreach ($chunk as $row) {
                            $row = (array)$row;
                            $values = [];
                            foreach ($row as $value) {
                                if (is_null($value)) {
                                    $values[] = 'NULL';
                                } else {
                                    // Escape string
                                    $value = addslashes($value);
                                    // Bersihkan line breaks
                                    $value = str_replace(["\n", "\r"], ["\\n", "\\r"], $value);
                                    $values[] = "'{$value}'";
                                }
                            }
                            $inserts[] = "(" . implode(', ', $values) . ")";
                        }
                        $sqlScript .= "INSERT INTO `{$tableName}` VALUES " . implode(", ", $inserts) . ";\n";
                    }
                }
                $sqlScript .= "\n\n";
            }

            $sqlScript .= "SET FOREIGN_KEY_CHECKS=1;\n";

            // Tulis file ke storage
            File::put($path, $sqlScript);

            if (File::exists($path) && filesize($path) > 0) {
                return response()->download($path, $filename)->deleteFileAfterSend(true);
            } else {
                return back()->with('error', 'File backup berhasil dibuat, namun file kosong (0 bytes).');
            }

        } catch (\Exception $e) {
            Log::error('Exception on backup: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat membuat backup: ' . $e->getMessage());
        }
    }

    /**
     * Upload dan Restore file .sql ke database saat ini.
     */
    public function restoreDatabase(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file', // Validasi strict mime sql terkadang sulit karena server mendeteksinya text/plain
        ]);

        $file = $request->file('sql_file');

        // Validasi tambahan untuk ektensi
        if ($file->getClientOriginalExtension() !== 'sql') {
            return back()->with('error', 'Format tidak didukung. Harap unggah file berakhiran .sql');
        }

        try {
            $sqlContent = File::get($file->getRealPath());

            // Nonaktifkan foreign key checks saat mere-store database
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            DB::unprepared($sqlContent);
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');

            return back()->with('success', 'Database berhasil di-restore dari file backup.');
        } catch (\Exception $e) {
            Log::error('Restore failed: ' . $e->getMessage());
            // Pastikan foreign key nyala kembali kalau gagal di tengah-tengah
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            return back()->with('error', 'Gagal memulihkan database. File mungkin korup atau format tidak sesuai. Error: ' . $e->getMessage());
        }
    }
}
