<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use App\Services\IncrementalBackupService;

Artisan::command('sso:link {email : Existing local account email} {idcode : Exact employee code; preserve leading zeros}', function () {
    $user = \App\Models\User::where('email', $this->argument('email'))->first();
    $code = $this->argument('idcode');
    if (! $user || ! is_string($code) || $code === '' || trim($code) !== $code || strlen($code) > 64) {
        $this->error('An existing account and an exact employee code (up to 64 characters) are required.');
        return 1;
    }
    if ($user->keycloak_subject !== null && (string) $user->idcode !== $code) {
        $this->error('This account is already linked. Review its identity before changing its employee code.');
        return 1;
    }
    if (\App\Models\User::where('idcode', $code)->whereKeyNot($user->id)->exists()) {
        $this->error('That employee code is already assigned to another account.');
        return 1;
    }
    $user->forceFill(['idcode' => $code])->save();
    $this->info('Employee code assigned to the existing storage account.');
    return 0;
})->purpose('Assign an employee code to an existing Rose Storage account without granting permissions');

Artisan::command('storage:health', function () {
    $this->info('WIPS Storage is ready.');
})->purpose('Check storage API availability');

Artisan::command('storage:backup {--limit= : Maximum objects per run}', function (IncrementalBackupService $backups) {
    if (! config('backups.enabled')) {
        $this->error('Backups are disabled. Set BACKUP_ENABLED=true to prepare encrypted ZIPs.');
        return 1;
    }
    $limit = filter_var($this->option('limit') ?? config('backups.limit'), FILTER_VALIDATE_INT);
    if ($limit === false || $limit < 0) {
        $this->error('Limit must be zero (all pending files) or a positive integer.');
        return 1;
    }
    // No expiry: never allow a second process to overwrite a running backup.
    $lock = Cache::lock('storage:incremental-backup', 0);
    if (! $lock->get()) {
        $this->error('Another backup is running.');
        return 1;
    }
    $copied = $failed = 0;
    try {
        $password = config('backups.password');
        if (! is_string($password) || strlen($password) < 12) {
            $this->error('Set BACKUP_ZIP_PASSWORD in .env to a password of at least 12 characters.');
            return 1;
        }
        $result = $backups->archive($password, $limit);
        $copied = $result['files'];
        $this->info($result['archive'] ? 'Encrypted ZIP saved: '.$result['archive'] : 'No pending uploads.');
        $this->info('Upload the ZIP to Google Drive yourself. This command does not upload or confirm an off-site copy.');
        $this->info("Verified: {$copied}; failed: {$failed}; pending: ".$backups->pending()->count());
    } catch (\Throwable $exception) {
        $failed = 1;
        $this->error('ZIP backup failed ('.$exception::class.'). No files were marked complete. Check PHP AES support, source files and temporary disk space, then retry.');
    } finally {
        $lock->release();
    }
    return $failed ? 1 : 0;
})->purpose('Prepare an AES-256 encrypted incremental ZIP for manual upload');

Artisan::command('storage:backup-status', function (IncrementalBackupService $backups) {
    $this->info('Enabled: '.(config('backups.enabled') ? 'yes' : 'no'));
    $this->info('Pending uploads: '.$backups->pending()->count());
    $this->info('Locally prepared object records: '.\Illuminate\Support\Facades\DB::table('object_backups')->count());
    $this->info('Last local verification: '.(\Illuminate\Support\Facades\DB::table('object_backups')->max('verified_at') ?? 'never'));
    $this->info('Off-site upload status is not tracked.');
})->purpose('Show incremental upload backup progress');
