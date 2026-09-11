<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>WIPS Storage</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: { 50: '#eef4ff', 100: '#dbe7ff', 500: '#356dff', 600: '#2458e8', 700: '#1d46bd', 950: '#0c1733' }
                    },
                    boxShadow: {
                        panel: '0 1px 2px rgba(15,23,42,.04), 0 16px 38px rgba(15,23,42,.07)',
                        glow: '0 20px 55px rgba(36,88,232,.25)'
                    }
                }
            }
        };
    </script>
    <style>
        [hidden], .hidden { display: none !important; }
        body { font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        .login-grid {
            background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 42px 42px;
        }
        #mainNav .nav-button { border-radius: .75rem; padding: .6rem .9rem; }
        #mainNav .nav-button.text-blue-700 { background: #eef4ff; color: #1d46bd; }
        .mobile-nav .nav-button { flex: 1 1 0%; border-radius: .65rem; }
        .mobile-nav .nav-button.text-blue-700 { background: #2458e8; color: #fff; }
        #loginStatus:not(:empty) { border-radius: .75rem; padding: .75rem 1rem; background: #fef2f2; border: 1px solid #fee2e2; }
        #appStatus:not(:empty) { position: fixed; z-index: 60; right: 1.5rem; top: 5.5rem; max-width: 24rem; border-radius: .75rem; padding: .8rem 1rem; background: #fff; box-shadow: 0 18px 40px rgba(15,23,42,.16); }
        .thin-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .thin-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">

<section id="loginPanel" class="min-h-screen lg:grid lg:grid-cols-[minmax(0,1.05fr)_minmax(500px,.95fr)]">
    <div class="login-grid relative hidden overflow-hidden bg-brand-950 px-12 py-10 text-white lg:flex lg:flex-col xl:px-20">
        <div class="absolute -left-32 top-1/3 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl"></div>
        <div class="absolute -right-20 -top-24 h-96 w-96 rounded-full bg-violet-400/20 blur-3xl"></div>

        <div class="relative z-10 flex items-center gap-3">
            <span class="grid h-11 w-11 place-items-center rounded-2xl bg-white text-brand-700 shadow-glow">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M4.8 8.3 12 4.7l7.2 3.6v7.4L12 19.3l-7.2-3.6V8.3Z"/><path d="m4.8 8.3 7.2 3.6 7.2-3.6M12 11.9v7.4"/>
                </svg>
            </span>
            <span><span class="block text-base font-extrabold tracking-tight">WIPS Storage</span><span class="block text-xs text-blue-200/70">Private object infrastructure</span></span>
        </div>

        <div class="relative z-10 my-auto max-w-xl py-14">
            <div class="inline-flex items-center gap-2 rounded-full border border-blue-300/20 bg-blue-300/10 px-3 py-1.5 text-xs font-semibold text-blue-100">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_12px_rgba(52,211,153,.9)]"></span>
                Secure storage control center
            </div>
            <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-[-0.045em] xl:text-5xl">Your files, organized and protected.</h1>
            <p class="mt-5 max-w-lg text-base leading-7 text-slate-300">Manage buckets, upload objects, and issue scoped credentials from one focused workspace.</p>

            <div class="mt-10 grid max-w-lg gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-white/[.06] p-4 backdrop-blur">
                    <svg class="h-5 w-5 text-blue-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v5c0 4.6 2.9 8.4 7 10 4.1-1.6 7-5.4 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/></svg>
                    <p class="mt-3 text-sm font-bold">Signed URLs</p><p class="mt-1 text-xs leading-5 text-slate-400">Time-limited private access</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/[.06] p-4 backdrop-blur">
                    <svg class="h-5 w-5 text-violet-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><ellipse cx="12" cy="6" rx="7" ry="3"/><path d="M5 6v6c0 1.7 3.1 3 7 3s7-1.3 7-3V6M5 12v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6"/></svg>
                    <p class="mt-3 text-sm font-bold">Buckets</p><p class="mt-1 text-xs leading-5 text-slate-400">Simple file organization</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/[.06] p-4 backdrop-blur">
                    <svg class="h-5 w-5 text-emerald-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="8" cy="15" r="3"/><path d="m10.5 13.5 7-7M15 6l3 3M14 10l2 2"/></svg>
                    <p class="mt-3 text-sm font-bold">Scoped tokens</p><p class="mt-1 text-xs leading-5 text-slate-400">Least-privilege credentials</p>
                </div>
            </div>
        </div>
        <p class="relative z-10 text-xs text-slate-500">WIPS Storage administration console</p>
    </div>

    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-white px-5 py-12 sm:px-10">
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-blue-50 blur-3xl lg:hidden"></div>
        <div class="relative w-full max-w-md">
            <div class="mb-10 flex items-center gap-3 lg:hidden">
                <span class="grid h-11 w-11 place-items-center rounded-2xl bg-brand-600 text-white shadow-lg shadow-blue-200">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4.8 8.3 12 4.7l7.2 3.6v7.4L12 19.3l-7.2-3.6V8.3Z"/><path d="m4.8 8.3 7.2 3.6 7.2-3.6M12 11.9v7.4"/></svg>
                </span>
                <span><span class="block font-extrabold tracking-tight">WIPS Storage</span><span class="block text-xs text-slate-500">Administration console</span></span>
            </div>

            <p class="text-sm font-bold text-brand-600">Admin portal</p>
            <h2 class="mt-2 text-3xl font-extrabold tracking-[-0.04em] text-slate-950">Welcome back</h2>
            <p class="mt-3 text-sm leading-6 text-slate-500">Enter your administrator credentials to continue.</p>

            <form id="loginForm" class="mt-8 space-y-5">
                <div>
                    <label for="email" class="mb-2 block text-sm font-bold text-slate-700">Email address</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 grid w-11 place-items-center text-slate-400"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg></span>
                        <input id="email" type="email" required autocomplete="email" placeholder="admin@example.com" class="block w-full rounded-xl border border-slate-200 bg-slate-50/70 py-3 pl-11 pr-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </div>
                </div>
                <div>
                    <label for="password" class="mb-2 block text-sm font-bold text-slate-700">Password</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 grid w-11 place-items-center text-slate-400"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span>
                        <input id="password" type="password" required autocomplete="current-password" placeholder="Enter your password" class="block w-full rounded-xl border border-slate-200 bg-slate-50/70 py-3 pl-11 pr-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
                    </div>
                </div>
                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-200 transition hover:-translate-y-0.5 hover:bg-brand-700 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-100">
                    Sign in to dashboard
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </form>
            <div id="loginStatus" class="mt-4 min-h-5 text-sm"></div>
            <div class="mt-8 flex items-center gap-3 text-xs text-slate-400"><span class="h-px flex-1 bg-slate-200"></span>Authorized access only<span class="h-px flex-1 bg-slate-200"></span></div>
        </div>
    </div>
</section>

<header id="appHeader" class="hidden sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl">
    <div class="mx-auto flex h-[72px] max-w-[1500px] items-center gap-6 px-4 sm:px-6 lg:px-10">
        <a href="/dashboard" class="flex shrink-0 items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-2xl bg-brand-600 text-white shadow-lg shadow-blue-200"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4.8 8.3 12 4.7l7.2 3.6v7.4L12 19.3l-7.2-3.6V8.3Z"/><path d="m4.8 8.3 7.2 3.6 7.2-3.6M12 11.9v7.4"/></svg></span>
            <span class="hidden sm:block"><span class="block text-sm font-extrabold tracking-tight text-slate-950">WIPS Storage</span><span class="block text-[10px] text-slate-400">Admin console</span></span>
        </a>

        <nav id="mainNav" class="hidden items-center gap-1 rounded-2xl bg-slate-100/80 p-1 md:flex">
            <button data-view="bucketsView" class="nav-button text-sm font-bold text-blue-700">Buckets</button>
            <button data-view="tokensView" class="nav-button text-sm font-bold text-slate-500 hover:text-slate-900">Access tokens</button>
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
            <button data-view="bucketsView" class="nav-button px-3 py-2 text-sm font-bold text-blue-700">Buckets</button>
            <button data-view="tokensView" class="nav-button px-3 py-2 text-sm font-bold text-slate-500">Access tokens</button>
        </div>
        <div id="appStatus" class="mb-4 min-h-5 text-sm"></div>

        <section id="bucketsView" class="view-panel">
            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                <div>
                    <div class="flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-[.16em] text-brand-600"><span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>Storage workspace</div>
                    <h1 class="mt-2 text-3xl font-extrabold tracking-[-0.04em] text-slate-950 sm:text-4xl">Object storage</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Create buckets, upload files, and control how every object is shared.</p>
                </div>
                <button id="newBucketTopButton" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-200 transition hover:-translate-y-0.5 hover:bg-brand-700"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>New bucket</button>
            </div>

            <div class="mt-7 grid gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
                <aside class="self-start rounded-2xl border border-slate-200 bg-white p-4 shadow-panel lg:sticky lg:top-28">
                    <div class="flex items-center justify-between px-1">
                        <div><h2 class="text-sm font-extrabold text-slate-950">Your buckets</h2><p class="mt-0.5 text-[11px] text-slate-400">Select a storage container</p></div>
                        <span id="bucketCount" class="rounded-full bg-brand-50 px-2.5 py-1 text-[10px] font-extrabold text-brand-700">0</span>
                    </div>
                    <div id="bucketList" class="thin-scrollbar mt-4 max-h-[380px] space-y-1.5 overflow-y-auto pr-1"></div>
                    <form id="bucketForm" class="mt-4 rounded-xl bg-slate-50 p-3">
                        <label for="bucketName" class="mb-2 block text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Create bucket</label>
                        <input id="bucketName" placeholder="product-images" pattern="[a-z0-9][a-z0-9.-]{1,61}[a-z0-9]" required class="block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-blue-100">
                        <button type="submit" class="mt-2.5 flex w-full items-center justify-center gap-2 rounded-lg bg-slate-900 px-3 py-2.5 text-xs font-bold text-white transition hover:bg-slate-700"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>Create bucket</button>
                    </form>
                </aside>

                <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-panel">
                    <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                        <div class="flex flex-col justify-between gap-5 xl:flex-row xl:items-center">
                            <div><div class="flex items-center gap-2"><h2 id="bucketTitle" class="text-lg font-extrabold tracking-tight text-slate-950">Select a bucket</h2><span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wider text-emerald-700">Available</span></div><p id="bucketHint" class="mt-1 text-xs text-slate-500">Choose a bucket to view its objects.</p></div>
                            <form id="uploadForm" class="hidden flex-col gap-2 sm:flex-row sm:items-center">
                                <input id="uploadFile" type="file" required class="block min-w-0 max-w-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-2.5 file:py-1 file:text-xs file:font-bold file:text-slate-700 sm:w-64">
                                <select id="uploadVisibility" aria-label="Visibility" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-bold text-slate-700 outline-none focus:border-brand-500 focus:ring-4 focus:ring-blue-100"><option value="private">Private</option><option value="public">Public</option></select>
                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 px-3.5 py-2.5 text-xs font-bold text-white transition hover:bg-brand-700"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 16V4M7 9l5-5 5 5"/><path d="M5 15v5h14v-5"/></svg>Upload</button>
                            </form>
                        </div>
                    </div>
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/60 px-5 py-3 text-[10px] font-semibold text-slate-400 sm:px-6"><span>OBJECTS</span><span class="flex items-center gap-1.5"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v5c0 4.6 2.9 8.4 7 10 4.1-1.6 7-5.4 7-10V6l-7-3Z"/></svg>Private URLs expire automatically</span></div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left">
                            <thead><tr class="border-b border-slate-100 text-[10px] font-extrabold uppercase tracking-[.12em] text-slate-400"><th class="px-5 py-3.5 sm:px-6">Object</th><th class="px-4 py-3.5">Type</th><th class="px-4 py-3.5">Size</th><th class="px-4 py-3.5">Access</th><th class="px-4 py-3.5">Created</th><th class="px-5 py-3.5 text-right sm:px-6">Actions</th></tr></thead>
                            <tbody id="objectTableBody" class="divide-y divide-slate-100"></tbody>
                        </table>
                    </div>
                    <div id="objectEmpty" class="hidden px-6 py-16 text-center">
                        <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-slate-400"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M7 3h7l4 4v14H7z"/><path d="M14 3v5h5M9.5 14.5h5M12 12v5"/></svg></span>
                        <p class="mt-4 text-sm font-extrabold text-slate-800">No objects in this bucket</p><p class="mt-1 text-xs text-slate-500">Upload a file to add the first object.</p>
                    </div>
                </section>
            </div>
        </section>

        <section id="tokensView" class="view-panel hidden">
            <div><div class="flex items-center gap-2 text-[11px] font-extrabold uppercase tracking-[.16em] text-violet-600"><span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>Security and access</div><h1 class="mt-2 text-3xl font-extrabold tracking-[-0.04em] text-slate-950 sm:text-4xl">Access tokens</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Issue least-privilege credentials for apps, integrations, and automated uploads.</p></div>

            <div class="mt-7 grid gap-6 lg:grid-cols-[360px_minmax(0,1fr)]">
                <div class="self-start rounded-2xl border border-slate-200 bg-white p-5 shadow-panel lg:sticky lg:top-28 sm:p-6">
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-violet-50 text-violet-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="8" cy="15" r="3"/><path d="m10.5 13.5 7-7M15 6l3 3M14 10l2 2"/></svg></span>
                    <h2 class="mt-4 text-lg font-extrabold tracking-tight text-slate-950">Create access token</h2><p class="mt-1 text-xs leading-5 text-slate-500">The secret value is displayed only once.</p>
                    <form id="tokenForm" class="mt-6 space-y-5">
                        <div><label for="tokenName" class="mb-2 block text-xs font-bold text-slate-700">Token name</label><input id="tokenName" placeholder="Production uploader" required maxlength="80" class="block w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3.5 py-2.5 text-sm outline-none placeholder:text-slate-400 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-blue-100"></div>
                        <div><label for="tokenExpiry" class="mb-2 block text-xs font-bold text-slate-700">Expires in seconds</label><input id="tokenExpiry" type="number" value="86400" min="60" max="31536000" required class="block w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3.5 py-2.5 text-sm outline-none focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-blue-100"><p class="mt-1.5 text-[10px] text-slate-400">86400 seconds equals 24 hours.</p></div>
                        <fieldset><legend class="mb-3 text-xs font-bold text-slate-700">Permissions</legend><div class="grid gap-2">
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50/40"><input type="checkbox" name="ability" value="buckets:read" checked class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">Read buckets</label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50/40"><input type="checkbox" name="ability" value="buckets:write" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">Create buckets</label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50/40"><input type="checkbox" name="ability" value="objects:read" checked class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">Read objects</label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50/40"><input type="checkbox" name="ability" value="objects:write" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">Upload / change visibility</label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50/40"><input type="checkbox" name="ability" value="objects:delete" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">Delete objects</label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 text-xs font-semibold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50/40"><input type="checkbox" name="ability" value="tokens:manage" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">Manage tokens</label>
                        </div></fieldset>
                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-200 transition hover:-translate-y-0.5 hover:bg-brand-700"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>Generate token</button>
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
                thumb.textContent = object.mime_type && object.mime_type.indexOf('image/') === 0 ? 'IMG' : 'FILE';
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
        if (!value) return '-';
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