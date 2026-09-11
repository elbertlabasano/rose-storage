# WIPS Storage API

A small Laravel object-storage API with the core S3-style workflow:

- authenticated users create buckets;
- users upload objects as multipart form data;
- objects stay on a private filesystem disk;
- public objects are served through a public URL;
- private access uses an application-signed URL with an expiration time.

## Requirements

- PHP 8.2+
- Composer
- SQLite (default) or MySQL
- The PHP fileinfo extension

## Setup

~~~
composer install
cp .env.example .env
php artisan key:generate
mkdir -p database
touch database/database.sqlite
php artisan migrate
php artisan serve
~~~

On Windows PowerShell, create the SQLite file with:

~~~
New-Item -ItemType File database/database.sqlite
~~~

Set APP_URL to the URL clients will use. The default local disk stores files under storage/app/private, which is deliberately not web-readable. To use S3 or an S3-compatible service, set FILESYSTEM_DISK=s3 and the normal AWS_* variables. Keep the bucket private; the Laravel download endpoints enforce visibility and signatures.

## API

All authenticated calls use Authorization: Bearer <token>.

Register:

~~~
POST /api/auth/register
Content-Type: application/json

{"name":"Ada","email":"ada@example.com","password":"a-strong-password","password_confirmation":"a-strong-password"}
~~~

Create a bucket:

~~~
POST /api/buckets
Authorization: Bearer <token>
Content-Type: application/json

{"name":"media"}
~~~

Upload:

~~~
curl -X POST http://localhost:8000/api/buckets/{bucket_id}/objects \
  -H "Authorization: Bearer {token}" \
  -F "file=@./photo.jpg" \
  -F "visibility=public"
~~~

The upload response includes public_url only for public objects. To issue a private URL:

~~~
POST /api/objects/{object_id}/private-url
Authorization: Bearer <token>
Content-Type: application/json

{"expires_in":3600}
~~~

expires_in is in seconds and is capped by PRIVATE_URL_MAX_SECONDS (24 hours by default). The returned URL contains Laravel's signed-route expires and signature parameters. It can be used without a bearer token until it expires:

~~~
curl -L "{private_url}" -o downloaded-file
~~~

Change visibility at any time:

~~~
PATCH /api/objects/{object_id}/visibility
Authorization: Bearer <token>
Content-Type: application/json

{"visibility":"private"}
~~~

## Security decisions

- Every bucket and object is owner-scoped. Unauthorized resources return 404 to avoid confirming that an ID exists.
- Object keys are server-generated UUID paths; client filenames are metadata only, so traversal and overwrite attacks are avoided.
- Uploads have an allowlist of file types and a configurable size limit (STORAGE_MAX_UPLOAD_MB, 50 MB by default). MIME type is detected from the uploaded content.
- Files are written to a private disk and streamed by Laravel with X-Content-Type-Options: nosniff.
- Private URLs are signed with APP_KEY, expire server-side, and are rate-limited when issued.
- Authentication, uploads, and URL issuance have separate rate limits.
- Login replaces prior api tokens for that user; logout revokes the current token, and Sanctum tokens expire after SANCTUM_DEFAULT_TOKEN_MINUTES (one day by default).
- Keep APP_DEBUG=false in production, serve only over HTTPS, rotate APP_KEY only with a migration plan, and use a least-privilege IAM user for S3.
- For production S3, use bucket-level public access blocks. The API's public URL is intentionally an application URL, not a public S3 object URL.

## Checks

~~~
php artisan test
php artisan route:list --path=api
~~~


Never commit .env.
Keep your S3 bucket private and enable Block Public Access.
Use a least-privilege AWS IAM user.
Use HTTPS only.
Keep private URLs short-lived, such as 15–60 minutes.
Add storage quotas to prevent users filling the disk.
Consider antivirus/content scanning for untrusted uploads.
Never expose Sanctum bearer tokens in browser JavaScript or URLs.
## Dashboard and access tokens

Open `/dashboard` after starting the app. Sign in with an API account to browse buckets, preview public images, upload files, create buckets, and request temporary links for private objects.

The dashboard also creates scoped Sanctum access tokens. A token is displayed only once. Give a client only the permissions it needs:

~~~
POST /api/access-tokens
Authorization: Bearer <full-access-token>
Content-Type: application/json

{
  "name": "Image uploader",
  "abilities": ["buckets:read", "objects:write"],
  "expires_in": 86400
}
~~~

Use the returned `token` as a bearer token from a core PHP client. Available abilities are `buckets:read`, `buckets:write`, `objects:read`, `objects:write`, `objects:delete`, and `tokens:manage`. List or revoke tokens with `GET /api/access-tokens` and `DELETE /api/access-tokens/{id}`. Never put an access token in a public URL or commit it to source control.
## Local admin seeder

Set the optional `SEED_ADMIN_*` values in `.env`, then run:

~~~powershell
php artisan db:seed
~~~

The local defaults are `admin@example.com` / `ChangeMeNow!123`. Change `SEED_ADMIN_PASSWORD` before using the seeder. The seeder is disabled when `APP_ENV=production`.