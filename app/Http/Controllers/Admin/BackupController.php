<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BackupLog;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class BackupController extends Controller
{
    public function index()
    {
        return view('admin.backup.index', [
            'logs' => BackupLog::latest()->paginate(10),
        ]);
    }

    public function export(): RedirectResponse
    {
        if (! $this->commandAvailable('mysqldump')) {
            $message = 'Export database gagal: utilitas `mysqldump` tidak tersedia di server.';
            BackupLog::create([
                'file_name' => '-',
                'file_path' => '-',
                'type' => 'database-export',
                'file_size' => 0,
                'status' => 'failed',
                'created_by' => auth()->id(),
                'notes' => $message,
            ]);

            ActivityLogger::log('backup', 'export-database-failed', null, ['reason' => $message]);

            return back()->withErrors(['backup' => $message]);
        }

        $filename = 'db-backup-' . now()->format('Ymd-His') . '.sql';
        $path = storage_path('app/backups/' . $filename);

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        $process = new Process([
            'mysqldump',
            '--host=' . config('database.connections.mysql.host'),
            '--port=' . config('database.connections.mysql.port'),
            '--user=' . config('database.connections.mysql.username'),
            '--password=' . config('database.connections.mysql.password'),
            '--result-file=' . $path,
            config('database.connections.mysql.database'),
        ]);

        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful() || ! file_exists($path)) {
            $message = 'Export database gagal: ' . trim($process->getErrorOutput() ?: 'Proses mysqldump tidak berhasil dijalankan.');

            BackupLog::create([
                'file_name' => $filename,
                'file_path' => 'backups/' . $filename,
                'type' => 'database-export',
                'file_size' => 0,
                'status' => 'failed',
                'created_by' => auth()->id(),
                'notes' => $message,
            ]);

            ActivityLogger::log('backup', 'export-database-failed', null, ['error' => $message]);

            return back()->withErrors(['backup' => $message]);
        }

        $relativePath = 'backups/' . $filename;
        $size = filesize($path) ?: 0;

        $log = BackupLog::create([
            'file_name' => $filename,
            'file_path' => $relativePath,
            'type' => 'database-export',
            'file_size' => $size,
            'status' => 'completed',
            'created_by' => auth()->id(),
            'notes' => 'Export SQL dari panel admin.',
        ]);

        ActivityLogger::log('backup', 'export-database', $log, [
            'file_name' => $filename,
            'file_size' => $size,
        ]);

        return back()->with('success', 'Export database berhasil dibuat.');
    }

    public function import(Request $request): RedirectResponse
    {
        if (! $this->commandAvailable('mysql')) {
            $message = 'Import database gagal: utilitas `mysql` client tidak tersedia di server.';

            BackupLog::create([
                'file_name' => '-',
                'file_path' => '-',
                'type' => 'database-import',
                'file_size' => 0,
                'status' => 'failed',
                'created_by' => auth()->id(),
                'notes' => $message,
            ]);

            ActivityLogger::log('backup', 'import-database-failed', null, ['reason' => $message]);

            return back()->withErrors(['backup' => $message]);
        }

        $data = $request->validate([
            'sql_file' => ['required', 'file', 'mimes:sql,txt', 'max:51200'],
        ]);

        $filename = 'db-import-' . now()->format('Ymd-His') . '.sql';
        $storedPath = $data['sql_file']->storeAs('backups/imports', $filename);
        $fullPath = storage_path('app/' . $storedPath);

        $process = new Process([
            'mysql',
            '--host=' . config('database.connections.mysql.host'),
            '--port=' . config('database.connections.mysql.port'),
            '--user=' . config('database.connections.mysql.username'),
            '--password=' . config('database.connections.mysql.password'),
            config('database.connections.mysql.database'),
        ]);
        $process->setInput(file_get_contents($fullPath) ?: '');
        $process->setTimeout(180);
        $process->run();

        $status = $process->isSuccessful() ? 'completed' : 'failed';
        $notes = $status === 'completed' ? 'Import SQL berhasil.' : trim($process->getErrorOutput() ?: 'Gagal menjalankan import SQL.');

        $log = BackupLog::create([
            'file_name' => $filename,
            'file_path' => $storedPath,
            'type' => 'database-import',
            'file_size' => filesize($fullPath) ?: 0,
            'status' => $status,
            'created_by' => auth()->id(),
            'notes' => $notes,
        ]);

        ActivityLogger::log('backup', $status === 'completed' ? 'import-database' : 'import-database-failed', $log, [
            'file_name' => $filename,
            'status' => $status,
            'notes' => $notes,
        ]);

        if ($status === 'failed') {
            return back()->withErrors(['backup' => 'Import database gagal: ' . $notes]);
        }

        return back()->with('success', 'Import database berhasil dijalankan.');
    }

    public function download(BackupLog $backupLog)
    {
        if (! Storage::disk('local')->exists($backupLog->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($backupLog->file_path, $backupLog->file_name);
    }

    protected function commandAvailable(string $command): bool
    {
        $process = new Process([$command, '--version']);
        $process->setTimeout(10);
        $process->run();

        return $process->isSuccessful();
    }
}
