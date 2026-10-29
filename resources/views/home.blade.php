<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Rose Storage keeps your images, documents, and application files organized in buckets, with controlled access and links you can share.">
    <title>Rose Storage — A home for every file</title>
    <link rel="icon" href="{{ asset('brand/rose-logo-transparent.png') }}">
    <link rel="stylesheet" href="{{ asset('css/rose-brand.css') . '?v=2' }}">
</head>
<body class="home-page">
@include('partials.brand-icons')
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header shell">
    <a class="brand-lockup" href="{{ route('home') }}" aria-label="Rose Storage home"><img src="{{ asset('brand/rose-logo-transparent.png') }}" alt="Rose Bakeshop" width="86" height="63"><span>Storage<span class="brand-caption">A home for every file</span></span></a>
    <nav aria-label="Main navigation"><a class="desktop-link" href="#about">What is object storage?</a><a class="desktop-link" href="#how-it-works">How it works</a><a class="button button-small" href="{{ route('login') }}">Sign in <svg><use href="#icon-arrow"/></svg></a></nav>
</header>
<main id="main">
    <section class="hero shell">
        <div class="hero-copy">
            <p class="eyebrow"><span class="small-sun">✳</span> YOUR FILES. OUR CARE.</p>
            <h1>Good things deserve<br><span class="script">a safe place.</span></h1>
            <p class="hero-description">Meet Rose Storage. A dedicated home for your images, documents, and application files—organized, protected, and ready when you need them.</p>
            <div class="hero-actions"><a class="button" href="{{ route('login') }}">Open your storage <svg><use href="#icon-arrow"/></svg></a><a class="text-link" href="#about">Get to know us <span>↘</span></a></div>
            <p class="hero-note"><svg><use href="#icon-lock"/></svg> Your files stay private until you choose to share.</p>
        </div>
        <div class="storage-scene" aria-label="Illustration of documents and images organized in a Rose Storage bucket">
            <div class="scene-orbit orbit-one"></div><div class="scene-orbit orbit-two"></div>
            <span class="scene-star star-one">✳</span><span class="scene-star star-two">✦</span>
            <div class="file-card file-card-back"><div class="sample-photo"><span></span><i></i></div><div><strong>brand-assets.png</strong><small>A little inspiration, saved.</small></div></div>
            <div class="file-card file-card-front"><span class="document-icon"><svg><use href="#icon-file"/></svg></span><div><strong>branch-report.pdf</strong><small>Documents · Private</small></div><span class="file-check">✓</span></div>
            <div class="bucket-illustration"><div class="bucket-top"><span></span><span></span><span></span></div><div class="bucket-content"><span class="bucket-icon"><svg><use href="#icon-box"/></svg></span><h2>Everything, in its place.</h2><p>One bucket. So many possibilities.</p><div class="bucket-footer"><span>rose-assets /</span><svg><use href="#icon-lock"/></svg></div></div></div>
            <div class="sharing-note"><span><svg><use href="#icon-link"/></svg></span><div><strong>Share on your terms</strong><small>Public or time-limited links</small></div></div>
            <span class="scene-label">A little order. A lot of peace of mind.</span>
        </div>
    </section>
    <div class="capabilities shell" aria-label="Storage capabilities"><span><svg><use href="#icon-box"/></svg> Organized in buckets</span><span><svg><use href="#icon-lock"/></svg> Access you control</span><span><svg><use href="#icon-link"/></svg> Ready to connect</span></div>
    <section id="about" class="about-section shell">
        <div><p class="eyebrow">LET’S KEEP IT SIMPLE</p><h2>Object storage.<br><span class="script">What’s that, exactly?</span></h2></div>
        <div class="about-copy"><p>Think of it as a digital storeroom. Each file is an <strong>object</strong>, kept with details like its name, size, and file type. Related files live together in containers called <strong>buckets</strong>.</p><p>Rose Storage gives your team and applications one place to upload, organize, and access those files. No hunting through scattered folders. Just the right file, in the right place.</p></div>
    </section>
    <section class="feature-section shell" aria-label="What you can do with Rose Storage">
        <article><span class="feature-icon feature-gold"><svg><use href="#icon-upload"/></svg></span><h3>A place for your everyday files</h3><p>From product photos and branch documents to PDFs and videos, keep your working files together in named buckets.</p></article>
        <article><span class="feature-icon feature-pink"><svg><use href="#icon-lock"/></svg></span><h3>A little more control</h3><p>Keep objects private, make selected files public, or create a private link that expires. You decide who gets access.</p></article>
        <article><span class="feature-icon feature-lavender"><svg><use href="#icon-link"/></svg></span><h3>Made to work with your apps</h3><p>Connect websites and internal tools through the API, with access tokens scoped to the actions each application needs.</p></article>
    </section>
    <section id="how-it-works" class="workflow-section shell"><div class="workflow-heading"><p class="eyebrow">FROM UPLOAD TO EVERYDAY USE</p><h2>A simple flow.<br><span class="script">Less file fuss.</span></h2><a class="text-link" href="{{ route('login') }}">Let’s get organized <span>↗</span></a></div><ol class="steps"><li><span>01</span><div><h3>Give your files a home</h3><p>Create a bucket for a project, a team, or an application.</p></div></li><li><span>02</span><div><h3>Bring your files in</h3><p>Upload files through the dashboard or connect your application.</p></div></li><li><span>03</span><div><h3>Use them your way</h3><p>Download when needed, share a link, or serve files to your apps.</p></div></li></ol></section>
    <section class="closing-section shell"><p class="eyebrow">A LITTLE SPACE FOR WHAT MATTERS</p><h2>Your files, <span class="script">right at home.</span></h2><p>Sign in with your Rose Storage account to get started.</p><a class="button" href="{{ route('login') }}">Go to sign in <svg><use href="#icon-arrow"/></svg></a></section>
</main>
@include('partials.brand-waves')
<footer class="site-footer shell"><span>Rose Storage <span class="footer-dot">·</span> Part of the Rose family</span><a href="{{ route('login') }}">Storage dashboard ↗</a><span>© {{ date('Y') }} Rose Bakeshop</span></footer>
</body>
</html>
