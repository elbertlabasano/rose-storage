<?php

namespace App\Http\Controllers;

use App\Models\StoredObject;
use App\Models\User;
use App\Services\IncrementalBackupService;
use App\Services\MysqlBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

class BackupController extends Controller
{
    public function index(IncrementalBackupService $files, MysqlBackupService $sql)
    {
        $folder = config('backups.google_drive_folder_id');
        return response()->json([
            'enabled' => config('backups.enabled'),
            'sql_available' => config('backups.enabled') && $sql->available(),
            'zip_available' => config('backups.enabled') && class_exists(\ZipArchive::class)
                && \ZipArchive::isEncryptionMethodSupported(\ZipArchive::EM_AES_256)
                && strlen((string) config('backups.password')) >= 12,
            'objects' => StoredObject::count(), 'bytes' => (int) StoredObject::sum('size'),
            'pending' => $files->pending()->count(),
            'drive_url' => is_string($folder) && preg_match('/\A[A-Za-z0-9_-]{10,200}\z/D', $folder)
                ? 'https://drive.google.com/drive/folders/'.$folder : null,
            'exports' => DB::table('backup_exports')->leftJoin('users', 'users.id', '=', 'backup_exports.user_id')
                ->orderByDesc('backup_exports.created_at')->limit(20)
                ->get(['backup_exports.id', 'type', 'filename', 'bytes', 'files', 'backup_exports.created_at', 'users.name as administrator']),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function prepare(Request $request, string $type, IncrementalBackupService $files, MysqlBackupService $sql)
    {
        abort_unless(config('backups.enabled'), 503, 'Backups are disabled.');
        $lock = Cache::lock($type === 'zip' ? 'storage:incremental-backup' : 'storage:web-sql-backup', 0);
        if (! $lock->get()) {
            return response()->json(['message' => 'Another backup is being prepared. Try again when it finishes.'], 409);
        }
        $temporary = null;
        try {
            @set_time_limit((int) config('backups.timeout'));
            if ($type === 'zip') {
                $password = config('backups.password');
                abort_unless(is_string($password) && strlen($password) >= 12, 503, 'The ZIP backup password must be configured.');
                $backup = $files->archive($password, config('backups.limit'), $request->user()->id);
                if (! $backup['archive']) {
                    return response()->json(['message' => 'No new or changed files. Re-download an earlier ZIP from Recent exports.'], 409);
                }
                return $this->downloadLink($request, $backup['export_id']);
            } else {
                $backup = $sql->create();
                $temporary = $backup['path'];
                $filename = $backup['name'];
                $stream = fopen($temporary, 'rb');
                try {
                    if (! Storage::disk(config('backups.disk'))->put($filename, $stream, ['visibility' => 'private'])) {
                        throw new \RuntimeException('Cannot retain SQL backup.');
                    }
                } finally {
                    if (is_resource($stream)) { fclose($stream); }
                }
                $count = 0;
            }
            $id = (string) Str::uuid();
            DB::table('backup_exports')->insert([
                'id' => $id, 'user_id' => $request->user()->id, 'type' => $type, 'filename' => $filename,
                'bytes' => Storage::disk(config('backups.disk'))->size($filename), 'files' => $count, 'created_at' => now(),
            ]);
            return $this->downloadLink($request, $id);
        } catch (Throwable $exception) {
            Log::warning('Manual backup preparation failed.', ['type' => $type, 'exception_class' => $exception::class]);
            return response()->json(['message' => 'The backup could not be prepared. Check backup configuration, source files and temporary disk space, then retry.'], 503);
        } finally {
            if ($temporary && is_file($temporary)) { unlink($temporary); }
            $lock->release();
        }
    }

    public function downloadLink(Request $request, string $id)
    {
        $export = $this->export($id);
        return response()->json([
            'filename' => $export->filename,
            'bytes' => (int) $export->bytes,
            'url' => URL::temporarySignedRoute('backups.download', now()->addMinutes(10), ['id' => $id, 'administrator' => $request->user()->id]),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function download(Request $request, string $id)
    {
        $administrator = (int) $request->query('administrator');
        abort_unless(in_array($administrator, config('backups.admin_user_ids'), true)
            && User::whereKey($administrator)->exists(), 403);
        $export = $this->export($id);
        if ($request->ajax()) {
            // The browser saves this response via a local blob URL. Without an
            // attachment filename/ZIP MIME type, external managers leave it alone.
            return response()->file(Storage::disk(config('backups.disk'))->path($export->filename), [
                'Content-Type' => 'application/octet-stream', 'Cache-Control' => 'private, no-store, max-age=0',
                'X-Content-Type-Options' => 'nosniff', 'Referrer-Policy' => 'no-referrer',
            ])->setPrivate();
        }
        return response()->download(Storage::disk(config('backups.disk'))->path($export->filename), $export->filename, [
            'Content-Type' => $export->type === 'zip' ? 'application/zip' : 'application/sql',
            'Cache-Control' => 'private, no-store, max-age=0', 'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'no-referrer',
        ])->setPrivate();
    }

    private function export(string $id): object
    {
        $export = DB::table('backup_exports')->where('id', $id)->first();
        abort_unless($export && basename($export->filename) === $export->filename
            && preg_match('/\ARose-Storage-[A-Za-z0-9_.-]+\.(zip|sql)\z/D', $export->filename)
            && Storage::disk(config('backups.disk'))->exists($export->filename), 404);
        return $export;
    }
}
