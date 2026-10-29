<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rose Storage — Your workspace</title>
    <link rel="icon" href="{{ asset('brand/rose-logo-transparent.png') }}">
    <link rel="stylesheet" href="{{ asset('css/rose-brand.css') . '?v=2' }}">
    <link rel="stylesheet" href="{{ asset('css/backups.css') }}">
    <link rel="stylesheet" href="{{ asset('css/file-listing.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: { 50: '#fff3e8', 100: '#ffeddc', 200: '#ffd4bc', 300: '#feb2a4', 400: '#ee6a60', 500: '#e3382d', 600: '#e31b17', 700: '#b41816', 800: '#8e1716', 900: '#701616', 950: '#5d1616' },
                        slate: { 50: '#fff9f4', 100: '#fff0e3', 200: '#eddbcd', 300: '#d8c1b1', 400: '#aa8a7b', 500: '#84665b', 600: '#715247', 700: '#5e3b31', 800: '#492825', 900: '#402223', 950: '#331c1d' }
                    },
                    boxShadow: {
                        panel: '0 1px 2px rgba(15,23,42,.04), 0 16px 38px rgba(15,23,42,.07)',
                        glow: '0 20px 55px rgba(227,27,23,.2)'
                    }
                }
            }
        };
    </script>
    <style>
        [hidden] { display: none !important; }
        .hidden { display: none; }
        body { font-family: Outfit, ui-sans-serif, system-ui, sans-serif; }
        .login-grid {
            background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 42px 42px;
        }
        #mainNav .nav-button { border-radius: .75rem; padding: .6rem .9rem; }
        #mainNav .nav-button.text-brand-700 { background: #fff3e8; color: #b41816; }
        .mobile-nav .nav-button { flex: 1 1 0%; border-radius: .65rem; }
        .mobile-nav .nav-button.text-brand-700 { background: #e31b17; color: #fff; }
        #loginStatus:not(:empty) { border-radius: .75rem; padding: .75rem 1rem; background: #fef2f2; border: 1px solid #fee2e2; }
        #appStatus:not(:empty) { position: fixed; z-index: 60; right: 1.5rem; top: 5.5rem; max-width: 24rem; border-radius: .75rem; padding: .8rem 1rem; background: #fff; box-shadow: 0 18px 40px rgba(15,23,42,.16); }
        .thin-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .thin-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
    </style>
</head>
<body class="rose-dashboard min-h-screen bg-slate-50 text-slate-900 antialiased">

@include('partials.brand-icons')
<div id="sessionLoading" role="status" style="min-height:100vh;display:grid;place-items:center;color:#84665b">Loading your workspace…</div>
<noscript><p>Enable JavaScript to sign in and use Rose Storage.</p></noscript>
@include('partials.login')

<header id="appHeader" class="hidden sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl">
    <div class="mx-auto flex h-[72px] max-w-[1500px] items-center gap-6 px-4 sm:px-6 lg:px-10">
        <a href="/dashboard" class="flex shrink-0 items-center gap-3">
            <img src="{{ asset('brand/rose-logo-transparent.png') }}" alt="Rose Bakeshop" class="h-10 w-14 rounded object-contain">
            <span class="hidden sm:block"><span class="block text-sm font-extrabold tracking-tight text-slate-950">Rose Storage</span><span class="block text-[10px] text-slate-400">Admin console</span></span>
        </a>

        <nav id="mainNav" class="hidden items-center gap-1 rounded-2xl bg-slate-100/80 p-1 md:flex">
            <button data-view="bucketsView" class="nav-button text-sm font-bold text-brand-700">Buckets</button>
            <button data-view="tokensView" class="nav-button text-sm font-bold text-slate-500 hover:text-slate-900">Access tokens</button>
            <button data-view="backupsView" data-backup-nav hidden class="nav-button text-sm font-bold text-slate-500 hover:text-slate-900">Backups</button>
        </nav>

        <div class="ml-auto flex items-center gap-3">
            <div class="hidden text-right lg:block"><p class="text-xs font-bold text-slate-700">Administrator</p><p id="userEmail" class="text-[10px] text-slate-400"></p></div>
            <span class="grid h-9 w-9 place-items-center rounded-xl bg-slate-900 text-[10px] font-extrabold text-white">AD</span>
            <button id="logoutButton" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50">Sign out</button>
        </div>
    </div>
</header>

<main id="appPanel" class="hidden min-h-[calc(100vh-72px)]">
    <div class="mx-auto max-w-[1500px] px-4 py-6 sm:px-6 lg:px-10 lg:py-9">
        <div class="mobile-nav mb-5 flex gap-2 rounded-xl border border-slate-200 bg-white p-1.5 shadow-sm md:hidden">
            <button data-view="bucketsView" class="nav-button px-3 py-2 text-sm font-bold text-brand-700">Buckets</button>
            <button data-view="tokensView" class="nav-button px-3 py-2 text-sm font-bold text-slate-500">Access tokens</button>
            <button data-view="backupsView" data-backup-nav hidden class="nav-button px-3 py-2 text-sm font-bold text-slate-500">Backups</button>
        </div>
        <div id="appStatus" class="mb-4 min-h-5 text-sm"></div>

        @include('partials.backups')
        @include('partials.bucket-listing')

        <section id="tokensView" class="view-panel hidden">
            <div><div class="flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-[.16em] text-violet-600"><span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>Security and access</div><h1 class="mt-2 text-3xl font-extrabold tracking-[-0.04em] text-slate-950 sm:text-4xl">Access tokens</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Issue least-privilege credentials for apps, integrations, and automated uploads.</p></div>

            <div class="mt-7 grid gap-6 lg:grid-cols-[360px_minmax(0,1fr)]">
                <div class="self-start rounded-2xl border border-slate-200 bg-white p-5 shadow-panel lg:sticky lg:top-28 sm:p-6">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-violet-50 text-violet-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="8" cy="15" r="3"/><path d="m10.5 13.5 7-7M15 6l3 3M14 10l2 2"/></svg></span>
                    <h2 class="mt-4 text-lg font-extrabold tracking-tight text-slate-950">Create access token</h2><p class="mt-1 text-xs leading-5 text-slate-500">The secret value is displayed only once.</p>
                    <form id="tokenForm" class="mt-6 space-y-5">
                        <div><label for="tokenName" class="mb-2 block text-xs font-bold text-slate-700">Token name</label><input id="tokenName" placeholder="Production uploader" required maxlength="80" class="block w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3.5 py-2.5 text-sm outline-none placeholder:text-slate-400 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-100"></div>
                        <div><label for="tokenExpiry" class="mb-2 block text-xs font-bold text-slate-700">Expires in seconds</label><input id="tokenExpiry" type="number" value="86400" min="60" max="31536000" required class="block w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-100"><p class="mt-1.5 text-[10px] text-slate-400">86400 seconds equals 24 hours.</p></div>
                        <fieldset><legend class="mb-3 text-xs font-bold text-slate-700">Permissions</legend><div class="grid gap-2">
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 text-xs font-semibold text-slate-600 transition hover:border-brand-200 hover:bg-brand-50/40"><input type="checkbox" name="ability" value="buckets:read" checked class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">Read buckets</label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 text-xs font-semibold text-slate-600 transition hover:border-brand-200 hover:bg-brand-50/40"><input type="checkbox" name="ability" value="buckets:write" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">Create buckets</label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 text-xs font-semibold text-slate-600 transition hover:border-brand-200 hover:bg-brand-50/40"><input type="checkbox" name="ability" value="objects:read" checked class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">Read objects</label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 text-xs font-semibold text-slate-600 transition hover:border-brand-200 hover:bg-brand-50/40"><input type="checkbox" name="ability" value="objects:write" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">Upload / change visibility</label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 text-xs font-semibold text-slate-600 transition hover:border-brand-200 hover:bg-brand-50/40"><input type="checkbox" name="ability" value="objects:delete" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">Delete objects</label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 text-xs font-semibold text-slate-600 transition hover:border-brand-200 hover:bg-brand-50/40"><input type="checkbox" name="ability" value="tokens:manage" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">Manage tokens</label>
                        </div></fieldset>
                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-brand-200 transition hover:-translate-y-0.5 hover:bg-brand-700"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>Generate token</button>
                    </form>
                    <div id="newToken" class="mt-4"></div>
                </div>

                <div class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-panel">
                    <div class="border-b border-slate-100 px-5 py-5 sm:px-6"><h2 class="text-base font-extrabold text-slate-950">Active credentials</h2><p class="mt-1 text-xs text-slate-500">Revoke credentials that are no longer required.</p></div>
                    <div id="tokenList" class="divide-y divide-slate-100 px-5 sm:px-6"></div>
                </div>
            </div>
        </section>
    </div>
</main>
<script>
(function () {
    'use strict';

    var token = sessionStorage.getItem('wips_dashboard_token') || '';
    var ssoSessionActive = @json(auth('web')->check() && session('rose_sso_authenticated') === true);
    var selectedBucket = null;
    var requestedView = location.pathname === '/backups' ? 'backupsView' : 'bucketsView';
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
        clearTimeout(element.statusTimer);
        if (element === appStatus && !error && message) {
            element.statusTimer = setTimeout(function () { element.textContent = ''; }, 6000);
        }
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
        if (!response.ok) {
            var error = new Error(body.message || (body.errors ? Object.values(body.errors).flat().join(' ') : 'Request failed'));
            error.status = response.status;
            throw error;
        }
        return body;
    }

    function showView(viewId) {
        requestedView = viewId;
        history.replaceState(null, '', viewId === 'backupsView' ? '/backups' : '/dashboard');
        document.querySelectorAll('.view-panel').forEach(function (panel) {
            panel.classList.toggle('hidden', panel.id !== viewId);
        });
        if (viewId === 'backupsView') loadBackups();
        document.querySelectorAll('.nav-button').forEach(function (button) {
            var active = button.dataset.view === viewId;
            button.className = 'nav-button rounded-lg px-3 py-2 text-sm font-semibold ' + (active ? 'bg-brand-50 text-brand-700' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900');
        });
    }

    function showApp(user) {
        document.getElementById('sessionLoading').hidden = true;
        loginPanel.hidden = true;
        loginPanel.classList.add('hidden');
        if (location.pathname === '/login') history.replaceState(null, '', '/dashboard');
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
        showView(requestedView);
        loadBuckets();
        loadTokens();
        if (requestedView !== 'backupsView') loadBackups(true);
    }

    function showLogin() {
        document.getElementById('sessionLoading').hidden = true;
        loginPanel.hidden = false;
        loginPanel.classList.remove('hidden');
        if (location.pathname === '/dashboard' || location.pathname === '/backups') history.replaceState(null, '', '/login');
        document.querySelectorAll('[data-backup-nav]').forEach(function (button) { button.hidden = true; });
        appPanel.classList.add('hidden');
        appHeader.classList.add('hidden');
        mainNav.classList.add('hidden');
    }

    @include('partials.file-listing-script')

    function formatBytes(bytes) {
        if (!bytes) return '0 B';
        var units = ['B', 'KB', 'MB', 'GB'];
        var index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
        return (bytes / Math.pow(1024, index)).toFixed(index ? 1 : 0) + ' ' + units[index];
    }

    function formatDate(value) {
        if (!value) return '-';
        var date = new Date(value);
        return isNaN(date.getTime()) ? value : date.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric', timeZone: 'Asia/Manila' });
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
        var submit = document.getElementById('loginSubmit');
        submit.disabled = true;
        submit.querySelector('span').textContent = 'Signing in…';
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
        finally {
            submit.disabled = false;
            submit.querySelector('span').textContent = 'Sign in to your storage';
        }
    });

    document.getElementById('togglePassword').addEventListener('click', function () {
        var password = document.getElementById('password');
        var visible = password.type === 'password';
        password.type = visible ? 'text' : 'password';
        this.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
        this.setAttribute('aria-pressed', String(visible));
    });

    document.getElementById('logoutButton').addEventListener('click', async function () {
        if (ssoSessionActive) {
            try {
                var signedOut = await fetch('/auth/keycloak/logout', {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                if (!signedOut.ok && signedOut.status !== 401) throw new Error('Sign-out could not complete. Please try again.');
                ssoSessionActive = false;
            } catch (error) { setStatus(appStatus, error.message, true); return; }
        }
        try { await api('/auth/logout', { method: 'POST' }); } catch (e) {}
        token = '';
        sessionStorage.removeItem('wips_dashboard_token');
        sessionStorage.removeItem('wips_dashboard_email');
        showLogin();
    });

    document.getElementById('bucketForm').addEventListener('submit', async function (event) {
        event.preventDefault();
        try {
            var created = await api('/buckets', { method: 'POST', body: JSON.stringify({ name: document.getElementById('bucketName').value }) });
            selectedBucket = created.data.id;
            document.getElementById('objectSearch').value = '';
            document.getElementById('objectVisibility').value = 'all';
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
        var uploadButton = document.getElementById('uploadSubmit');
        uploadButton.disabled = true;
        uploadButton.querySelector('span').textContent = 'Uploading…';
        var form = new FormData();
        form.append('file', file);
        form.append('visibility', document.getElementById('uploadVisibility').value);
        try {
            await api('/buckets/' + encodeURIComponent(selectedBucket) + '/objects', { method: 'POST', body: form });
            document.getElementById('uploadFile').value = '';
            document.getElementById('objectSearch').value = '';
            setStatus(appStatus, 'Upload complete.', false);
            await loadBuckets();
        } catch (error) { setStatus(appStatus, error.message, true); }
        finally {
            uploadButton.disabled = false;
            uploadButton.querySelector('span').textContent = 'Upload';
        }
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

    var backupBusy = false;
    async function loadBackups(silent) {
        try {
            var data = await api('/backups');
            document.querySelectorAll('[data-backup-nav]').forEach(function (button) { button.hidden = false; });
            document.getElementById('downloadSqlBackup').disabled = backupBusy || !data.sql_available;
            document.getElementById('downloadZipBackup').disabled = backupBusy || !data.zip_available || !data.pending;
            document.getElementById('backupFileSummary').textContent = data.objects + ' registered files · ' + formatBytes(data.bytes) + ' before compression · ' + data.pending + ' new or changed. Preparation can take a moment.';
            document.getElementById('backupAvailability').textContent = !data.enabled ? 'Backups are disabled in server configuration.' : !data.zip_available ? 'Encrypted ZIP export needs a configured password and PHP AES support.' : !data.sql_available ? 'SQL export is unavailable. Ask your administrator to configure mysqldump.' : !data.pending ? 'All current files have been exported. Re-download an earlier ZIP below.' : '';
            var drive = document.getElementById('backupDriveFolder');
            drive.href = data.drive_url || 'https://drive.google.com/drive/my-drive';
            drive.firstChild.textContent = data.drive_url ? 'Open Drive folder ' : 'Open Google Drive ';
            document.getElementById('backupDriveHint').textContent = data.drive_url ? 'Opens the configured backup folder in a new tab.' : 'Choose your backup folder in Drive. No folder link has been configured.';
            var rows = document.getElementById('backupExportList');
            rows.textContent = '';
            if (!data.exports.length) {
                var empty = document.createElement('tr');
                var cell = document.createElement('td');
                cell.colSpan = 6;
                cell.textContent = 'No exports yet. Prepare your first backup above.';
                empty.appendChild(cell);
                rows.appendChild(empty);
            }
            data.exports.forEach(function (item) {
                var row = document.createElement('tr');
                var date = new Date(item.created_at.replace(' ', 'T') + (/(Z|[+-]\d{2}:\d{2})$/.test(item.created_at) ? '' : 'Z'));
                [isNaN(date) ? item.created_at : new Intl.DateTimeFormat('en-PH', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Manila' }).format(date) + ' PHT', item.filename, item.type === 'zip' ? 'Encrypted file ZIP' : 'Database SQL', formatBytes(item.bytes), item.administrator || 'Server command'].forEach(function (value) {
                    var cell = document.createElement('td'); cell.textContent = value; row.appendChild(cell);
                });
                var action = document.createElement('td');
                var button = document.createElement('button');
                button.type = 'button'; button.className = 'backup-secondary backup-action'; button.textContent = 'Download';
                button.addEventListener('click', function () { downloadBackup('/backups/' + encodeURIComponent(item.id) + '/download', false); });
                action.appendChild(button); row.appendChild(action); rows.appendChild(row);
            });
        } catch (error) {
            document.querySelectorAll('[data-backup-nav]').forEach(function (button) { button.hidden = true; });
            if (!silent) {
                document.getElementById('backupStatus').textContent = error.status === 403 ? 'Backups are available only to configured administrators signed in with full access.' : error.message;
                document.getElementById('backupExportList').textContent = '';
            }
        }
    }

    async function downloadBackup(path, preparing) {
        if (backupBusy) return;
        backupBusy = true;
        var status = document.getElementById('backupStatus');
        status.textContent = preparing ? 'Preparing your backup. Large exports can take time; keep this page open.' : 'Preparing a private download link…';
        document.querySelectorAll('.backup-action').forEach(function (button) { button.disabled = true; });
        try {
            var result = await api(path, { method: 'POST' });
            var downloadUrl = result.url;
            // Buffer only small downloads, avoiding external managers intercepting
            // signed HTTP links. Large backups keep the normal streaming path.
            if (Number.isFinite(result.bytes) && result.bytes <= 64 * 1024 * 1024) {
                status.textContent = 'Retrieving your backup for download…';
                var response = await fetch(result.url, { cache: 'no-store', referrerPolicy: 'no-referrer', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (response.status !== 200) throw new Error('The backup could not be retrieved. Refresh the list and try Download again.');
                var blob = await response.blob();
                if (blob.size !== result.bytes) throw new Error('The backup download was incomplete. Please try again.');
                downloadUrl = URL.createObjectURL(blob);
                setTimeout(function () { URL.revokeObjectURL(downloadUrl); }, 60000);
            }
            var download = document.createElement('a');
            download.href = downloadUrl; download.download = result.filename; download.referrerPolicy = 'no-referrer';
            document.body.appendChild(download); download.click(); download.remove();
            status.textContent = 'Your download is ready. Save it, then upload it to your restricted Drive folder.';
        } catch (error) { status.textContent = error.message; }
        finally { backupBusy = false; await loadBackups(true); }
    }
    document.getElementById('downloadSqlBackup').addEventListener('click', function () { downloadBackup('/backups/prepare/sql', true); });
    document.getElementById('downloadZipBackup').addEventListener('click', function () { downloadBackup('/backups/prepare/zip', true); });
    document.getElementById('refreshBackups').addEventListener('click', function () { loadBackups(); });

    async function startDashboard() {
        if (ssoSessionActive) {
            try {
                var response = await fetch('/auth/keycloak/session', {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                if (!response.ok) throw new Error('Your Rose Storage session could not be restored. Sign in again.');
                var session = await response.json();
                token = session.token;
                sessionStorage.setItem('wips_dashboard_token', token);
                showApp(session.user);
                return;
            } catch (error) {
                token = ''; sessionStorage.removeItem('wips_dashboard_token');
                showLogin(); setStatus(document.getElementById('loginStatus'), error.message, true);
                return;
            }
        }
        if (token) showApp(); else showLogin();
    }
    startDashboard();
}());
</script>
</body>
</html>
