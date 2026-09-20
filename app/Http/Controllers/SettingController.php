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
     * Jalankan proses ekspor/backup database ke file .sql
     */
    public function backupDatabase()
    {
        $database = env('DB_DATABASE');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');

        $filename = 'backup_' . $database . '_' . date('Y-m-d_H-i-s') . '.sql';
        $path = storage_path('app/private/' . $filename);

        // Pastikan direktori ada
        if (!File::exists(storage_path('app/private'))) {
            File::makeDirectory(storage_path('app/private'), 0755, true);
        }

        // Susun perintah mysqldump.
        // Catatan: Gunakan path mysqldump secara spesifik jika berada di windows (XAMPP).
        // Kita berasumsi mysqldump bisa diakses secara global, atau fallback ke path XAMPP umum.
        $mysqldumpPath = 'mysqldump';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if (File::exists('C:\xampp\mysql\bin\mysqldump.exe')) {
                $mysqldumpPath = '"C:\xampp\mysql\bin\mysqldump.exe"';
            }
        }

        $passwordArg = empty($password) ? '' : "--password={$password}";
        $command = "{$mysqldumpPath} --user={$username} {$passwordArg} --host={$host} --port={$port} {$database} > \"{$path}\" 2>&1";

        try {
            // Fix Windows 10106 (WSAEPROVIDERFAILEDINIT) error
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                putenv('SystemRoot=C:\Windows');
                putenv('WINDIR=C:\Windows');
            }

            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                $errorMsg = implode("\n", $output);
                Log::error('Backup failed: ' . $errorMsg);
                return back()->with('error', 'Gagal membackup database. Pastikan mysqldump tersedia di sistem Anda. Pesan: ' . $errorMsg);
            }

            if (File::exists($path) && filesize($path) > 0) {
                return response()->download($path, $filename)->deleteFileAfterSend(true);
            } else {
                return back()->with('error', 'File backup berhasil dibuat, namun file kosong (0 bytes).');
            }

        } catch (\Exception $e) {
            Log::error('Exception on backup: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
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
