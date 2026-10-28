# Manual encrypted backups

## Download from the dashboard

Sign in as a configured administrator and select **Backups**, or open `/backups`.
Click **Download SQL** and **Download encrypted file ZIP**. Preparation completes
on the server, then your browser downloads the file. The ZIP password comes from
`BACKUP_ZIP_PASSWORD`; no prompt appears. SQL is not encrypted and includes sensitive
account/token records, so keep it in restricted storage.

**Recent backup exports** lets administrators download retained files again without
creating a new export. Links expire after ten minutes. Files remain private on the
server; the history confirms preparation, not successful download or Drive upload.
New command-line ZIP exports also appear in the history as **Server command**.

Server configuration:

- `BACKUP_ADMIN_USER_IDS`: comma-separated existing user IDs allowed to export the
  whole installation. Default is empty (no access). Only full-access login tokens
  qualify; scoped application tokens cannot export backups.
- `BACKUP_MYSQL_DUMP_BINARY`: path to `mysqldump` or `mariadb-dump`. SQL export is
  available only with MySQL/MariaDB and a working dump binary. The local XAMPP setup
  uses `C:/xampp/mysql/bin/mysqldump.exe`.
- `BACKUP_GOOGLE_DRIVE_FOLDER_ID`: optional folder ID for the **Open Drive folder**
  link. This is only a link; no Google OAuth credentials are needed. If blank, the
  page opens Google Drive so you can choose a folder yourself.
- `BACKUP_TIMEOUT`: preparation time limit in seconds (300 by default). Proxy/PHP
  request limits may be shorter. Use terminal exports or smaller file batches if
  the web server times out; a failed browser request does not prove preparation
  failed. Refresh history before retrying.

No automatic upload or schedule is enabled. The server retains SQL/ZIP exports
until an administrator removes them; deleting a retained file makes its download
unavailable. Restrict access to `storage/app/private/backups` and never expose it
through a public storage symlink.

For downloads up to 64 MiB, the page retrieves the file through an AJAX response
and saves it from a local browser URL. This avoids IDM intercepting the signed
ZIP attachment request. The page checks the received byte count before saving.
Larger exports use a normal streaming download to avoid buffering the entire file
in browser memory. If IDM interferes with those downloads, exclude the storage
site from its browser integration, then request a fresh link from Recent exports.

## Create a ZIP from the terminal

Run the database migrations once, then create a ZIP manually:

```sh
php artisan migrate
php artisan storage:backup
```

Set `BACKUP_ZIP_PASSWORD` in the server's `.env` to a password of at least 12
characters. The command reads it through Laravel configuration and never prompts.
After changing `.env`, run `php artisan config:clear` (or rebuild your production
configuration cache). There is no command-line password option. Never commit `.env`.
Store the password securely: a lost password means the archive cannot be recovered.
Changing the setting only affects future ZIPs; retain old passwords for existing ZIPs.

The command creates an AES-256 encrypted ZIP under `storage/app/private/backups`
and prints the full path. All file entries, the manifest, and recovery instructions
are encrypted. ZIP filenames and entry paths remain visible without a password;
encryption protects contents, not the ZIP directory listing. Extract with software
that supports AES-encrypted ZIPs. PHP/libzip AES support is checked before creation.

Upload the ZIP yourself through Google Drive's **New > File upload** action.
No Google API, OAuth credentials, scheduler, or network upload is used by this app.
Confirm the upload in Drive before considering the backup safely off-site.

The first run includes all registered uploads. Later runs include new or changed
files. Keep all required incremental ZIPs and their passwords, plus a separate
database backup. `storage:backup-status` reports local preparation only; it does
not know whether you uploaded any ZIP. Removing a local archive does not reset
its completion records, so keep it until its off-site copy has been confirmed.

Use `php artisan storage:backup --limit=100` to include at most 100 pending files.
Default `BACKUP_BATCH_SIZE=0` includes all pending uploads. Temporary disk space
must fit selected source files plus their ZIP. Private staging files are removed
after success/failure, but a killed process can leave a workspace under
`storage/framework/backups/{uuid}`. Completed encrypted ZIPs remain until you
remove them yourself. Never serve the backup folder publicly.

The ZIP contains `files/buckets/...`, `manifest.json`, and `README.txt`. Each source
checksum is verified before encryption, every encrypted file is decrypted and
verified after creation, and the retained ZIP is checked before completion is
recorded. Missing/corrupt source files abort the run. Uploads deleted before
their first successful export cannot be recovered. These are per-object snapshots,
not a transactionally consistent database snapshot. Restore database records
separately, then copy verified bytes back to their original disk/key using the manifest.

Concurrent runs are blocked by a persistent cache lock. After a killed process,
confirm no backup is running before releasing `storage:incremental-backup` via
`Cache::lock('storage:incremental-backup')->forceRelease()` in Artisan Tinker.
Use a shared persistent cache if multiple servers can prepare backups.
