<?php

namespace App\Services;

use App\Models\StoredObject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class IncrementalBackupService
{
    private function destination(): string
    {
        return hash('sha256', 'manual-aes-zip-v1:'.config('backups.disk'));
    }

    public function pending()
    {
        return StoredObject::whereNotExists(function ($query) {
            $query->selectRaw('1')->from('object_backups')
                ->whereColumn('object_backups.object_id', 'objects.id')
                ->whereColumn('object_backups.checksum', 'objects.checksum')
                ->whereColumn('object_backups.verified_at', '>=', 'objects.updated_at')
                ->where('destination', $this->destination());
        });
    }

    public function archive(#[\SensitiveParameter] string $password, int $limit = 0, ?int $administrator = null): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('PHP ZIP extension is required.');
        }
        if (strlen($password) < 12) {
            throw new RuntimeException('Use a backup password of at least 12 characters.');
        }
        if (! ZipArchive::isEncryptionMethodSupported(ZipArchive::EM_AES_256, true)
            || ! ZipArchive::isEncryptionMethodSupported(ZipArchive::EM_AES_256, false)) {
            throw new RuntimeException('This PHP ZIP build must support AES-256 encryption and decryption.');
        }
        if (! $this->pending()->exists()) {
            return ['files' => 0, 'archive' => null];
        }
        $started = now();
        $directory = storage_path('framework/backups/'.Str::uuid());
        $key = 'Rose-Storage-'.now('Asia/Manila')->format('Y-m-d_His').'-'.Str::uuid().'.zip';
        $path = $directory.'/backup.zip';
        $mask = umask(0077);
        try {
            if (! mkdir($directory, 0700, true) && ! is_dir($directory)) {
                throw new RuntimeException('Private backup workspace is unavailable.');
            }
        } finally {
            umask($mask);
        }
        $zip = new ZipArchive;
        $manifest = $records = null;
        $opened = false;
        try {
            if ($zip->open($path, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
                throw new RuntimeException('Cannot create ZIP.');
            }
            $opened = true;
            if (! $zip->setPassword($password)) {
                throw new RuntimeException('Cannot configure ZIP password.');
            }
            $manifest = fopen($directory.'/manifest.json', 'xb');
            $records = fopen($directory.'/records.jsonl', 'xb');
            if (! $manifest || ! $records) {
                throw new RuntimeException('Cannot create backup metadata.');
            }
            $this->write($manifest, '{"format":"rose-storage-incremental-zip","version":1,"created_at":'.json_encode($started->toIso8601String()).',"objects":[');
            $count = 0;
            foreach ($this->pending()->with('bucket')->lazyById(100) as $object) {
                if ($limit > 0 && $count >= $limit) {
                    break;
                }
                if (! preg_match('~\Abuckets/[a-f0-9-]+/[a-f0-9-]+\.[a-z0-9]+\z~D', $object->key)) {
                    throw new RuntimeException('Unsupported object key.');
                }
                $source = Storage::disk($object->disk)->readStream($object->key);
                if (! is_resource($source)) {
                    throw new RuntimeException('An upload is unreadable.');
                }
                $staged = $directory.'/file-'.$count;
                $output = fopen($staged, 'xb');
                try {
                    if (! $output || stream_copy_to_stream($source, $output) !== (int) $object->size) {
                        throw new RuntimeException('An upload could not be staged.');
                    }
                } finally {
                    fclose($source);
                    if (is_resource($output)) {
                        fclose($output);
                    }
                }
                if (! hash_equals($object->checksum, hash_file('sha256', $staged))) {
                    throw new RuntimeException('An upload checksum does not match.');
                }
                $entry = [
                    'object' => $object->getAttributes(), 'bucket' => $object->bucket?->getAttributes(),
                    'archive' => $key, 'archive_path' => 'files/'.$object->key,
                ];
                $json = json_encode($entry, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
                $this->write($manifest, ($count ? ',' : '').$json);
                $this->write($records, $json."\n");
                if (! $zip->addFile($staged, $entry['archive_path'])) {
                    throw new RuntimeException('Cannot add upload to ZIP.');
                }
                $count++;
            }
            $this->write($manifest, '],"file_count":'.$count.'}');
            fclose($manifest);
            fclose($records);
            $manifest = $records = null;
            if (! $zip->addFile($directory.'/manifest.json', 'manifest.json')
                || ! $zip->addFromString('README.txt', "Rose Storage incremental backup\nKeep all incremental ZIPs and a separate database backup.\nmanifest.json maps original objects and buckets to files/ entries. Verify SHA-256 before recovery.\nRestore database records separately and copy bytes to each original disk/key.\nThis archive contains only pending uploads, not a complete database or point-in-time snapshot.\n")) {
                throw new RuntimeException('Cannot complete ZIP metadata.');
            }
            for ($index = 0; $index < $zip->numFiles; $index++) {
                if (! $zip->setEncryptionIndex($index, ZipArchive::EM_AES_256)) {
                    throw new RuntimeException('Cannot encrypt a ZIP entry.');
                }
            }
            $closed = $zip->close();
            $opened = false;
            if (! $closed) {
                throw new RuntimeException('ZIP creation failed.');
            }
            $bytes = filesize($path);
            $checksum = hash_file('sha256', $path);
            // Verify decryption of every entry before retaining the encrypted archive.
            if ($zip->open($path) !== true || ! $zip->setPassword($password)) {
                throw new RuntimeException('Cannot verify encrypted ZIP.');
            }
            $opened = true;
            $records = fopen($directory.'/records.jsonl', 'rb');
            while (($line = fgets($records)) !== false) {
                $entry = json_decode($line, true, flags: JSON_THROW_ON_ERROR);
                $entryStream = $zip->getStream($entry['archive_path']);
                if (! is_resource($entryStream)) {
                    throw new RuntimeException('Cannot decrypt ZIP entry.');
                }
                try {
                    $hash = hash_init('sha256');
                    $entryBytes = hash_update_stream($hash, $entryStream);
                    $entryChecksum = hash_final($hash);
                } finally {
                    fclose($entryStream);
                }
                if ($entryBytes !== (int) $entry['object']['size'] || ! hash_equals($entry['object']['checksum'], $entryChecksum)) {
                    throw new RuntimeException('Encrypted ZIP entry verification failed.');
                }
            }
            fclose($records);
            $records = null;
            if ($zip->getFromName('manifest.json') === false || $zip->getFromName('README.txt') === false) {
                throw new RuntimeException('Encrypted ZIP metadata verification failed.');
            }
            $zip->close();
            $opened = false;
            $target = Storage::disk(config('backups.disk'));
            $stream = fopen($path, 'rb');
            try {
                if (! $target->put($key, $stream, ['visibility' => 'private', 'size' => $bytes])) {
                    throw new RuntimeException('Cannot retain encrypted ZIP.');
                }
            } finally {
                fclose($stream);
            }
            $stream = $target->readStream($key);
            if (! is_resource($stream)) {
                throw new RuntimeException('Saved ZIP is unreadable.');
            }
            try {
                $hash = hash_init('sha256');
                $read = hash_update_stream($hash, $stream);
                $verified = hash_final($hash);
            } finally {
                fclose($stream);
            }
            if ($read !== $bytes || ! hash_equals($checksum, $verified)) {
                $target->delete($key);
                throw new RuntimeException('Saved ZIP checksum does not match.');
            }
            $records = fopen($directory.'/records.jsonl', 'rb');
            $exportId = (string) Str::uuid();
            DB::transaction(function () use ($records, $started, $exportId, $administrator, $key, $bytes, $count) {
                while (($line = fgets($records)) !== false) {
                    $entry = json_decode($line, true, flags: JSON_THROW_ON_ERROR);
                    DB::table('object_backups')->updateOrInsert(['object_id' => $entry['object']['id']], [
                        'destination' => $this->destination(), 'checksum' => $entry['object']['checksum'],
                        'manifest' => $line, 'verified_at' => $started,
                    ]);
                }
                DB::table('backup_exports')->insert([
                    'id' => $exportId, 'user_id' => $administrator, 'type' => 'zip', 'filename' => $key,
                    'bytes' => $bytes, 'files' => $count, 'created_at' => now(),
                ]);
            });
            return ['files' => $count, 'archive' => $target->path($key), 'export_id' => $exportId];
        } finally {
            if ($opened) {
                try { $zip->close(); } catch (\Throwable) { }
            }
            foreach ([$manifest, $records] as $stream) {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
            File::deleteDirectory($directory);
        }
    }

    private function write($stream, string $text): void
    {
        if (fwrite($stream, $text) !== strlen($text)) {
            throw new RuntimeException('Backup workspace is full or unavailable.');
        }
    }
}
