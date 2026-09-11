<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>WIPS Storage Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ink: '#111827',
                        brand: '#2563eb',
                        mist: '#f5f7fb'
                    }
                }
            }
        };
    </script>
</head>
<body class="min-h-screen bg-mist text-slate-900 antialiased">
<header id="appHeader" class="hidden sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto flex h-20 max-w-7xl items-center gap-8 px-5 lg:px-8">
        <a href="/dashboard" class="flex shrink-0 items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-blue-600 text-white shadow-sm">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M5 8.5 12 5l7 3.5v7L12 19l-7-3.5v-7Z"/>
                    <path d="m5 8.5 7 3.5 7-3.5M12 12v7"/>
                </svg>
            </span>
            <span>
                <span class="block text-sm font-bold tracking-tight text-slate-950">WIPS Storage</span>
                <span class="block text-xs text-slate-500">Object storage dashboard</span>
            </span>
        </a>

        <nav id="mainNav" class="hidden items-center gap-1 md:flex">
            <button data-view="bucketsView" class="nav-button rounded-lg px-3 py-2 text-sm font-semibold text-blue-700">Buckets</button>
            <button data-view="tokensView" class="nav-button rounded-lg px-3 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-100 hover:text-slate-900">Access tokens</button>
        </nav>

        <div class="ml-auto flex items-center gap-3">
            <span id="userEmail" class="hidden text-sm text-slate-500 lg:inline"></span>
            <button id="logoutButton" class="rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">Sign out</button>
        </div>
    </div>
</header>

<main class="mx-auto max-w-7xl px-5 py-8 lg:px-8">
    <section id="loginPanel" class="mx-auto mt-10 max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <div class="mb-7">
            <div class="mb-4 grid h-12 w-12 place-items-center rounded-xl bg-blue-600 text-white">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M5 8.5 12 5l7 3.5v7L12 19l-7-3.5v-7Z"/>
                    <path d="m5 8.5 7 3.5 7-3.5M12 12v7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-950">Welcome back</h1>
            <p class="mt-1 text-sm text-slate-500">Sign in to manage your storage.</p>
        </div>
        <form id="loginForm" class="space-y-4">
            <div>
                <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">Email</label>
                <input id="email" type="email" required autocomplete="email" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>
            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Password</label>
                <input id="password" type="password" required autocomplete="current-password" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
            </div>
            <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">Sign in</button>
        </form>
        <div id="loginStatus" class="mt-4 min-h-5 text-sm"></div>
        <p class="mt-5 text-xs text-slate-500">Create an account first with <code class="rounded bg-slate-100 px-1.5 py-0.5">POST /api/auth/register</code>.</p>
    </section>

    <section id="appPanel" class="hidden">
        <div id="appStatus" class="mb-4 min-h-5 text-sm"></div>

        <section id="bucketsView" class="view-panel">
            <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                <div>
                    <p class="text-sm font-semibold text-blue-600">Workspace</p>
                    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Buckets</h1>
                    <p class="mt-1 text-sm text-slate-500">Organize and securely manage your stored objects.</p>
                </div>
                <button id="newBucketTopButton" class="hidden rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 sm:block">New bucket</button>
            </div>

            <div class="grid gap-5 lg:grid-cols-[260px_minmax(0,1fr)]">
                <aside class="space-y-5">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="mb-3 flex items-center justify-between">
                            <h2 class="text-sm font-bold text-slate-950">Your buckets</h2>
                            <span id="bucketCount" class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">0</span>
                        </div>
                        <div id="bucketList" class="space-y-1.5"></div>
                        <form id="bucketForm" class="mt-4 border-t border-slate-100 pt-4">
                            <label for="bucketName" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">New bucket</label>
                            <div class="flex gap-2">
                                <input id="bucketName" placeholder="images" pattern="[a-z0-9][a-z0-9.-]{1,61}[a-z0-9]" required class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700">Add</button>
                            </div>
                        </form>
                    </div>
                </aside>

                <section class="min-w-0 rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
                        <div class="flex flex-col justify-between gap-4 xl:flex-row xl:items-center">
                            <div>
                                <h2 id="bucketTitle" class="text-xl font-bold tracking-tight text-slate-950">Select a bucket</h2>
                                <p id="bucketHint" class="mt-1 text-sm text-slate-500">Your images and files will appear here.</p>
                            </div>
                            <form id="uploadForm" class="hidden flex-col gap-2 sm:flex-row sm:items-center">
                                <input id="uploadFile" type="file" required class="block w-full max-w-xs rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 file:mr-3 file:border-0 file:bg-slate-100 file:px-2.5 file:py-1 file:text-xs file:font-semibold sm:w-auto">
                                <select id="uploadVisibility" aria-label="Visibility" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                    <option value="private">Private</option>
                                    <option value="public">Public</option>
                                </select>
                                <button type="submit" class="rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-blue-700">Upload</button>
                            </form>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left">
                            <thead class="border-b border-slate-100 bg-slate-50/80">
                                <tr class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                    <th class="px-5 py-3 sm:px-6">Name</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">Size</th>
                                    <th class="px-4 py-3">Access</th>
                                    <th class="px-4 py-3">Created</th>
                                    <th class="px-5 py-3 text-right sm:px-6">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="objectTableBody" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                    <div id="objectEmpty" class="hidden px-6 py-14 text-center">
                        <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-2xl">◫</div>
                        <p class="mt-3 text-sm font-semibold text-slate-800">No objects in this bucket</p>
                        <p class="mt-1 text-sm text-slate-500">Upload an image or file to get started.</p>
                    </div>
                </section>
            </div>
        </section>

        <section id="tokensView" class="view-panel hidden">
            <div class="mb-6">
                <p class="text-sm font-semibold text-blue-600">Security</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Access tokens</h1>
                <p class="mt-1 text-sm text-slate-500">Create scoped credentials for client applications and revoke them anytime.</p>
            </div>

            <div class="grid gap-5 lg:grid-cols-[340px_minmax(0,1fr)]">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-bold text-slate-950">Create a token</h2>
                    <p class="mt-1 text-sm text-slate-500">The secret is shown only once.</p>
                    <form id="tokenForm" class="mt-5 space-y-4">
                        <div>
                            <label for="tokenName" class="mb-1.5 block text-sm font-semibold text-slate-700">Token name</label>
                            <input id="tokenName" placeholder="Image uploader" required maxlength="80" class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>
                        <div>
                            <label for="tokenExpiry" class="mb-1.5 block text-sm font-semibold text-slate-700">Expires in seconds</label>
                            <input id="tokenExpiry" type="number" value="86400" min="60" max="31536000" required class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>
                        <fieldset>
                            <legend class="mb-2 text-sm font-semibold text-slate-700">Permissions</legend>
                            <div class="space-y-2 text-sm text-slate-600">
                                <label class="flex items-center gap-2"><input type="checkbox" name="ability" value="buckets:read" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Read buckets</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="ability" value="buckets:write" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Create buckets</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="ability" value="objects:read" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Read objects</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="ability" value="objects:write" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Upload / change visibility</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="ability" value="objects:delete" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Delete objects</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="ability" value="tokens:manage" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Manage tokens</label>
                            </div>
                        </fieldset>
                        <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Create token</button>
                    </form>
                    <div id="newToken" class="mt-4"></div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
                        <h2 class="text-base font-bold text-slate-950">Active tokens</h2>
                    </div>
                    <div id="tokenList" class="divide-y divide-slate-100 px-5 sm:px-6"></div>
                </div>
            </div>
        </section>
    </section>
</main>

<script>
(function () {
    'use strict';

    var token = sessionStorage.getItem('wips_dashboard_token') || '';
    var selectedBucket = null;
    var loginPanel = document.getElementById('loginPanel');
    var appPanel = document.getElementById('appPanel');
    var appHeader = document.getElementById('appHeader');
    var mainNav = document.getElementById('mainNav');
    var bucketList = document.getElementById('bucketList');
    var objectTableBody = document.getElementById('objectTableBody');
    var objectEmpty = document.getElementById('objectEmpty');
    var appStatus = document.getElementById('appStatus');

    function setStatus(element, message, error) {
        element.textContent = message || '';
        element.className = 'mb-4 min-h-5 text-sm ' + (error ? 'text-red-600' : 'text-emerald-600');
    }

    async function api(path, options) {
        options = options || {};
        var headers = options.headers || {};
        headers.Accept = 'application/json';
        if (token) headers.Authorization = 'Bearer ' + token;
        if (!(options.body instanceof FormData)) headers['Content-Type'] = 'application/json';
        options.headers = headers;
        var response = await fetch('/api' + path, options);
        var body = {};
        try { body = await response.json(); } catch (e) {}
        if (!response.ok) throw new Error(body.message || (body.errors ? Object.values(body.errors).flat().join(' ') : 'Request failed'));
        return body;
    }

    function showView(viewId) {
        document.querySelectorAll('.view-panel').forEach(function (panel) {
            panel.classList.toggle('hidden', panel.id !== viewId);
        });
        document.querySelectorAll('.nav-button').forEach(function (button) {
            var active = button.dataset.view === viewId;
            button.className = 'nav-button rounded-lg px-3 py-2 text-sm font-semibold ' + (active ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900');
        });
    }

    function showApp(user) {
        loginPanel.classList.add('hidden');
        appPanel.classList.remove('hidden');
        appHeader.classList.remove('hidden');
        mainNav.classList.remove('hidden');
        if (user && user.email) {
            document.getElementById('userEmail').textContent = user.email;
            document.getElementById('userEmail').classList.remove('hidden');
            sessionStorage.setItem('wips_dashboard_email', user.email);
        } else {
            var savedEmail = sessionStorage.getItem('wips_dashboard_email');
            if (savedEmail) {
                document.getElementById('userEmail').textContent = savedEmail;
                document.getElementById('userEmail').classList.remove('hidden');
            }
        }
        showView('bucketsView');
        loadBuckets();
        loadTokens();
    }

    function showLogin() {
        loginPanel.classList.remove('hidden');
        appPanel.classList.add('hidden');
        appHeader.classList.add('hidden');
        mainNav.classList.add('hidden');
    }

    async function loadBuckets() {
        try {
            var response = await api('/buckets');
            var page = response.data || {};
            var buckets = page.data || [];
            var count = document.getElementById('bucketCount');
            count.textContent = buckets.length;
            bucketList.textContent = '';
            if (!buckets.length) {
                var empty = document.createElement('p');
                empty.className = 'rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-500';
                empty.textContent = 'No buckets yet.';
                bucketList.appendChild(empty);
                return;
            }
            buckets.forEach(function (bucket) {
                var button = document.createElement('button');
                button.type = 'button';
                button.textContent = bucket.name;
                button.dataset.id = bucket.id;
                button.className = 'w-full rounded-lg px-3 py-2 text-left text-sm font-semibold text-slate-600 transition hover:bg-slate-100';
                button.addEventListener('click', function () { selectBucket(bucket.id, bucket.name); });
                bucketList.appendChild(button);
            });
            if (!selectedBucket) selectBucket(buckets[0].id, buckets[0].name);
            else {
                var current = buckets.find(function (bucket) { return bucket.id === selectedBucket; });
                if (current) selectBucket(current.id, current.name);
            }
        } catch (error) { setStatus(appStatus, error.message, true); }
    }

    async function selectBucket(id, name) {
        selectedBucket = id;
        document.getElementById('bucketTitle').textContent = name;
        document.getElementById('uploadForm').classList.remove('hidden');
        Array.from(bucketList.children).forEach(function (button) {
            if (button.dataset && button.dataset.id) {
                var active = button.dataset.id === id;
                button.className = 'w-full rounded-lg px-3 py-2 text-left text-sm font-semibold transition ' + (active ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100');
            }
        });
        try {
            var response = await api('/buckets/' + encodeURIComponent(id));
            var objects = response.data.objects || [];
            document.getElementById('bucketHint').textContent = objects.length + (objects.length === 1 ? ' object' : ' objects') + ' in this bucket';
            renderObjects(objects);
        } catch (error) { setStatus(appStatus, error.message, true); }
    }

    function addCell(row, value, className) {
        var cell = document.createElement('td');
        cell.className = className || 'px-4 py-4 text-sm text-slate-600';
        if (value instanceof Node) cell.appendChild(value);
        else cell.textContent = value;
        row.appendChild(cell);
        return cell;
    }

    function renderObjects(objects) {
        objectTableBody.textContent = '';
        objectEmpty.classList.toggle('hidden', objects.length > 0);
        objectTableBody.parentElement.classList.toggle('hidden', objects.length === 0);
        objects.forEach(function (object) {
            var row = document.createElement('tr');
            row.className = 'group hover:bg-slate-50/80';

            var nameWrap = document.createElement('div');
            nameWrap.className = 'flex min-w-[220px] items-center gap-3';
            var thumb = document.createElement('div');
            thumb.className = 'grid h-9 w-9 shrink-0 place-items-center overflow-hidden rounded-lg bg-slate-100 text-lg';
            if (object.mime_type && object.mime_type.indexOf('image/') === 0 && object.public_url) {
                var image = document.createElement('img');
                image.src = object.public_url;
                image.alt = object.name;
                image.loading = 'lazy';
                image.className = 'h-full w-full object-cover';
                thumb.appendChild(image);
            } else {
                thumb.textContent = object.mime_type && object.mime_type.indexOf('image/') === 0 ? '🔒' : '📄';
            }
            var name = document.createElement('div');
            name.className = 'max-w-[260px] truncate font-semibold text-slate-800';
            name.title = object.name;
            name.textContent = object.name;
            nameWrap.appendChild(thumb);
            nameWrap.appendChild(name);
            addCell(row, nameWrap, 'px-5 py-4 sm:px-6');

            addCell(row, object.mime_type || 'unknown', 'whitespace-nowrap px-4 py-4 text-xs text-slate-500');
            addCell(row, formatBytes(object.size), 'whitespace-nowrap px-4 py-4 text-sm text-slate-600');

            var access = document.createElement('span');
            access.className = 'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ' + (object.visibility === 'public' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700');
            access.textContent = object.visibility;
            addCell(row, access);

            addCell(row, formatDate(object.created_at), 'whitespace-nowrap px-4 py-4 text-xs text-slate-500');

            var actions = document.createElement('div');
            actions.className = 'flex justify-end gap-2';
            var download = document.createElement('button');
            download.className = 'whitespace-nowrap rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700';
            download.textContent = 'Download';
            download.addEventListener('click', function () { downloadObject(object); });
            var copy = document.createElement('button');
            copy.className = 'whitespace-nowrap rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50';
            copy.textContent = 'Copy URL';
            copy.addEventListener('click', function () { copyObjectUrl(object); });
            actions.appendChild(download);
            actions.appendChild(copy);
            addCell(row, actions, 'px-5 py-4 text-right sm:px-6');
            objectTableBody.appendChild(row);
        });
    }

    function formatBytes(bytes) {
        if (!bytes) return '0 B';
        var units = ['B', 'KB', 'MB', 'GB'];
        var index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
        return (bytes / Math.pow(1024, index)).toFixed(index ? 1 : 0) + ' ' + units[index];
    }

    function formatDate(value) {
        if (!value) return '—';
        var date = new Date(value);
        return isNaN(date.getTime()) ? value : date.toLocaleDateString();
    }

    async function getObjectUrl(object) {
        if (object.public_url) return object.public_url;
        var response = await api('/objects/' + object.id + '/private-url', {
            method: 'POST',
            body: JSON.stringify({ expires_in: 900 })
        });
        return response.url;
    }

    async function downloadObject(object) {
        try {
            var url = await getObjectUrl(object);
            var link = document.createElement('a');
            link.href = url;
            link.download = object.name;
            link.rel = 'noopener';
            document.body.appendChild(link);
            link.click();
            link.remove();
            setStatus(appStatus, 'Download started.', false);
        } catch (error) { setStatus(appStatus, error.message, true); }
    }

    async function copyObjectUrl(object) {
        try {
            var url = await getObjectUrl(object);
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(url);
            } else {
                var textarea = document.createElement('textarea');
                textarea.value = url;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                textarea.remove();
            }
            setStatus(appStatus, 'URL copied to clipboard.', false);
        } catch (error) { setStatus(appStatus, error.message, true); }
    }

    document.querySelectorAll('.nav-button').forEach(function (button) {
        button.addEventListener('click', function () { showView(button.dataset.view); });
    });

    document.getElementById('loginForm').addEventListener('submit', async function (event) {
        event.preventDefault();
        var status = document.getElementById('loginStatus');
        try {
            var response = await api('/auth/login', { method: 'POST', body: JSON.stringify({
                email: document.getElementById('email').value,
                password: document.getElementById('password').value
            })});
            token = response.token;
            sessionStorage.setItem('wips_dashboard_token', token);
            setStatus(status, 'Signed in.', false);
            showApp(response.user);
        } catch (error) { setStatus(status, error.message, true); }
    });

    document.getElementById('logoutButton').addEventListener('click', async function () {
        try { await api('/auth/logout', { method: 'POST' }); } catch (e) {}
        token = '';
        sessionStorage.removeItem('wips_dashboard_token');
        sessionStorage.removeItem('wips_dashboard_email');
        showLogin();
    });

    document.getElementById('bucketForm').addEventListener('submit', async function (event) {
        event.preventDefault();
        try {
            await api('/buckets', { method: 'POST', body: JSON.stringify({ name: document.getElementById('bucketName').value }) });
            document.getElementById('bucketName').value = '';
            setStatus(appStatus, 'Bucket created.', false);
            await loadBuckets();
        } catch (error) { setStatus(appStatus, error.message, true); }
    });

    document.getElementById('newBucketTopButton').addEventListener('click', function () {
        document.getElementById('bucketName').focus();
    });

    document.getElementById('uploadForm').addEventListener('submit', async function (event) {
        event.preventDefault();
        var file = document.getElementById('uploadFile').files[0];
        if (!file || !selectedBucket) return;
        var form = new FormData();
        form.append('file', file);
        form.append('visibility', document.getElementById('uploadVisibility').value);
        try {
            await api('/buckets/' + encodeURIComponent(selectedBucket) + '/objects', { method: 'POST', body: form });
            document.getElementById('uploadFile').value = '';
            setStatus(appStatus, 'Upload complete.', false);
            await selectBucket(selectedBucket, document.getElementById('bucketTitle').textContent);
            await loadBuckets();
        } catch (error) { setStatus(appStatus, error.message, true); }
    });

    async function loadTokens() {
        try {
            var response = await api('/access-tokens');
            var list = document.getElementById('tokenList');
            list.textContent = '';
            var tokens = response.data || [];
            if (!tokens.length) {
                var empty = document.createElement('p');
                empty.className = 'py-10 text-center text-sm text-slate-500';
                empty.textContent = 'No access tokens yet.';
                list.appendChild(empty);
                return;
            }
            tokens.forEach(function (item) {
                var row = document.createElement('div');
                row.className = 'grid gap-3 py-4 md:grid-cols-[1.2fr_1.6fr_1fr_auto] md:items-center';
                var title = document.createElement('div');
                title.className = 'font-semibold text-slate-800';
                title.textContent = item.name;
                var details = document.createElement('div');
                details.className = 'text-xs text-slate-500';
                details.textContent = item.abilities.join(', ');
                var expiry = document.createElement('div');
                expiry.className = 'text-xs text-slate-500';
                expiry.textContent = 'Expires ' + formatDate(item.expires_at);
                var revoke = document.createElement('button');
                revoke.className = 'w-fit rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100';
                revoke.textContent = 'Revoke';
                revoke.addEventListener('click', async function () {
                    if (!confirm('Revoke this token?')) return;
                    try { await api('/access-tokens/' + item.id, { method: 'DELETE' }); loadTokens(); }
                    catch (error) { setStatus(appStatus, error.message, true); }
                });
                row.appendChild(title);
                row.appendChild(details);
                row.appendChild(expiry);
                row.appendChild(revoke);
                list.appendChild(row);
            });
        } catch (error) { setStatus(appStatus, error.message, true); }
    }

    document.getElementById('tokenForm').addEventListener('submit', async function (event) {
        event.preventDefault();
        var abilities = Array.from(document.querySelectorAll('input[name="ability"]:checked')).map(function (input) { return input.value; });
        var output = document.getElementById('newToken');
        try {
            var response = await api('/access-tokens', { method: 'POST', body: JSON.stringify({
                name: document.getElementById('tokenName').value,
                expires_in: Number(document.getElementById('tokenExpiry').value),
                abilities: abilities
            })});
            output.textContent = '';
            var warning = document.createElement('p');
            warning.className = 'rounded-lg bg-amber-50 p-3 text-xs font-medium text-amber-800';
            warning.textContent = response.warning;
            var secret = document.createElement('code');
            secret.className = 'mt-2 block break-all rounded-lg bg-slate-950 p-3 text-xs text-emerald-300';
            secret.textContent = response.token;
            output.appendChild(warning);
            output.appendChild(secret);
            loadTokens();
        } catch (error) { setStatus(appStatus, error.message, true); }
    });

    if (token) showApp(); else showLogin();
}());
</script>
</body>
</html>