<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Upload files and read objects with the Rose Storage API.">
    <title>Storage API guide — Rose Storage</title>
    <link rel="icon" href="{{ asset('brand/rose-logo-transparent.png') }}">
    <link rel="stylesheet" href="{{ asset('css/rose-brand.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/storage-api.css') }}">
</head>
<body class="home-page">
@include('partials.brand-icons')
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header shell">
    <a class="brand-lockup" href="{{ route('home') }}"><img src="{{ asset('brand/rose-logo-transparent.png') }}" alt="Rose Bakeshop" width="86" height="63"><span>Storage<span class="brand-caption">Developer guide</span></span></a>
    <nav aria-label="Main navigation"><a class="button button-small" href="{{ route('dashboard') }}">Open dashboard <svg><use href="#icon-arrow"/></svg></a></nav>
</header>
<main id="main" class="shell api-guide">
    <div class="api-intro"><p class="eyebrow">STORAGE API</p><h1>Upload once.<br><span class="script">Read when you need it.</span></h1><p>A practical guide to uploading files and reading objects from your application.</p></div>
    <nav class="api-sections" aria-label="On this page"><a href="#prepare">Before you start</a><a href="#upload">Upload</a><a href="#metadata">Read metadata</a><a href="#download">Read file contents</a><a href="#errors">Errors</a></nav>
    <section id="prepare"><p class="eyebrow">01 / PREPARE</p><h2>Bring a bucket and an access token</h2>
        <ol><li>Sign in to the <a href="{{ route('dashboard') }}">dashboard</a>. Select an existing bucket and copy its <strong>Bucket ID</strong>.</li><li>Under <strong>Access tokens</strong>, create a token with <code>objects:write</code> for uploads and <code>objects:read</code> for metadata and private links. Copy the token when it is shown.</li><li>Use the origin of your storage installation as <code>BASE_URL</code>. This page is served from <code>{{ request()->getSchemeAndHttpHost() }}</code>.</li></ol>
        <p>Examples below use Bash and cURL. In Windows, use Git Bash or WSL. Replace the placeholder values before running them.</p>
        <div class="api-code"><button type="button" class="copy-code">Copy</button><pre><code>BASE_URL='https://storage.rosebakeshop.net'
TOKEN='YOUR_ACCESS_TOKEN'
BUCKET_ID='YOUR_BUCKET_UUID'</code></pre></div>
        <aside>Keep the access token on your backend. Do not put it in public browser JavaScript, source control, or file URLs. The token can access only objects and buckets owned by its user.</aside>
    </section>
    <section id="upload"><p class="eyebrow">02 / UPLOAD</p><h2>Send a file to your bucket</h2><p><span class="api-method">POST</span> <code>/api/buckets/{bucket_id}/objects</code> · Requires <code>objects:write</code></p>
        <p>Send <code>multipart/form-data</code> with a required <code>file</code> field. The optional <code>visibility</code> field accepts <code>private</code> (the default) or <code>public</code>. Let cURL set the multipart Content-Type and boundary.</p>
        <div class="api-code"><button type="button" class="copy-code">Copy</button><pre><code>curl --fail-with-body "$BASE_URL/api/buckets/$BUCKET_ID/objects" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Accept: application/json' \
  -F 'file=@/path/to/photo.png' \
  -F 'visibility=private'</code></pre></div>
        <p>The current application limit is <strong>{{ config('storage.max_upload_mb') }} MB per file</strong>. Your web server or proxy may impose a smaller limit. Allowed file types: {{ implode(', ', config('storage.allowed_mimes')) }}. Validation checks the detected file type.</p>
        <h3>Successful response · 201 Created</h3><p>Save <code>data.id</code> as the object ID. It is different from the bucket ID. Example response (values abbreviated):</p>
        <div class="api-code"><button type="button" class="copy-code">Copy</button><pre><code>{
  "data": {
    "id": "OBJECT_UUID",
    "bucket_id": "BUCKET_UUID",
    "name": "photo.png",
    "mime_type": "image/png",
    "size": 18432,
    "checksum": "SHA256_HEX_DIGEST",
    "visibility": "private",
    "created_at": "2026-10-30T08:00:00.000000Z",
    "public_url": null
  }
}</code></pre></div>
    </section>
    <section id="metadata"><p class="eyebrow">03 / METADATA</p><h2>Read an object's details</h2><p><span class="api-method">GET</span> <code>/api/objects/{object_id}</code> · Requires <code>objects:read</code></p><p>This returns JSON metadata in the same <code>data</code> structure as the upload response. File size is in bytes. It does not return the file contents.</p>
        <div class="api-code"><button type="button" class="copy-code">Copy</button><pre><code>OBJECT_ID='YOUR_OBJECT_UUID'

curl --fail-with-body "$BASE_URL/api/objects/$OBJECT_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Accept: application/json'</code></pre></div>
    </section>
    <section id="download"><p class="eyebrow">04 / FILE CONTENTS</p><h2>Read or download the file</h2><h3>Public objects</h3><p>Upload with <code>visibility=public</code> to receive a <code>data.public_url</code>. Anyone with that URL can download the file without a token. Use the returned URL; the endpoint is <code>GET /api/objects/{object_id}/public</code>.</p>
        <div class="api-code"><button type="button" class="copy-code">Copy</button><pre><code>curl --fail-with-body "$BASE_URL/api/objects/$OBJECT_ID/public" \
  --output photo.png</code></pre></div>
        <h3>Private objects</h3><p>First request a signed download URL from your backend:</p><p><span class="api-method">POST</span> <code>/api/objects/{object_id}/private-url</code> · Requires <code>objects:read</code></p>
        <div class="api-code"><button type="button" class="copy-code">Copy</button><pre><code>curl --fail-with-body -X POST "$BASE_URL/api/objects/$OBJECT_ID/private-url" \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  --data '{"expires_in": 300}'</code></pre></div>
        <p>Returns <code>200 OK</code> with top-level <code>url</code> and <code>expires_at</code> fields (no <code>data</code> wrapper). <code>expires_in</code> is in seconds, defaults to 3600, and accepts 1–{{ config('storage.private_url_max_seconds') }} seconds.</p>
        <div class="api-code"><button type="button" class="copy-code">Copy</button><pre><code>{
  "url": "https://storage.rosebakeshop.net/api/objects/OBJECT_UUID/private?expires=...&amp;signature=...",
  "expires_at": "2026-10-30T08:05:00.000000Z"
}</code></pre></div>
        <p>Copy the full returned <code>url</code> and download it without an Authorization header. Keep its query string intact and quote it in your shell.</p>
        <div class="api-code"><button type="button" class="copy-code">Copy</button><pre><code>DOWNLOAD_URL='PASTE_THE_FULL_RETURNED_URL_HERE'
curl --fail-with-body "$DOWNLOAD_URL" --output photo.png</code></pre></div>
        <aside>A signed URL grants access to anyone who has it until it expires. Use a short lifetime and avoid logging or publicly sharing it. Generate a new URL when it expires.</aside>
        <p>Both download routes return the file bytes with its MIME type and an attachment filename. A browser normally downloads the file when you open the URL.</p>
    </section>
    <section id="errors"><p class="eyebrow">WHEN A REQUEST FAILS</p><h2>Common responses</h2><dl class="api-errors"><dt>401 Unauthorized</dt><dd>The bearer token is missing, invalid, revoked, or expired.</dd><dt>403 Forbidden</dt><dd>The token lacks the required ability, or the signed download link is invalid or expired.</dd><dt>404 Not Found</dt><dd>The object or bucket does not exist or belongs to another user. Public downloads also return this for private objects.</dd><dt>422 Unprocessable Content</dt><dd>Check the JSON <code>errors</code> fields for invalid files, visibility, or expiry values.</dd><dt>413 Payload Too Large</dt><dd>The upload exceeds a web server or proxy request limit.</dd><dt>429 Too Many Requests</dt><dd>Uploads or private-link requests have hit their rate limit. Wait before retrying.</dd></dl><p>Metadata and upload requests should include <code>Accept: application/json</code> to receive API error responses.</p></section>
</main>
<footer class="site-footer shell"><span>Rose Storage · Upload &amp; read API guide</span><a href="{{ route('dashboard') }}">Back to dashboard</a></footer>
<script>
document.querySelectorAll('.copy-code').forEach(function (button) {
    button.addEventListener('click', async function () {
        try {
            await navigator.clipboard.writeText(button.parentElement.querySelector('code').textContent);
            button.textContent = 'Copied';
        } catch (error) { button.textContent = 'Select and copy text'; }
        setTimeout(function () { button.textContent = 'Copy'; }, 2500);
    });
});
</script>
</body>
</html>
