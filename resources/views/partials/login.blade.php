<section id="loginPanel" class="rose-login" aria-label="Sign in to Rose Storage" hidden>
    <header class="login-header">
        <a class="brand-lockup" href="{{ route('home') }}" aria-label="Rose Storage home"><img src="{{ asset('brand/rose-logo-transparent.png') }}" alt="Rose Bakeshop" width="80" height="59"><span>Storage<span class="brand-caption">A home for every file</span></span></a>
        <a class="login-home-link" href="{{ route('home') }}">← Back to home</a>
    </header>
    <div class="login-rings" aria-hidden="true"></div>
    <div class="login-center">
        <div class="signin-card">
            <div class="signin-emblem" aria-hidden="true"><svg><use href="#icon-box"/></svg></div>
            <p class="eyebrow">YOUR ROSE STORAGE WORKSPACE</p>
            <h1>Good to have you back.</h1>
            <p class="signin-description">A little space for everything that matters.<br>Sign in to make yourself at home.</p>
            @error('sso')<p class="sso-error" role="alert">{{ $message }}</p>@enderror
            <form id="loginForm" class="signin-form">
                <label for="email">Email address</label>
                <div class="signin-field">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m3 7 9 6 9-6"/></svg>
                    <input id="email" name="email" type="email" required autocomplete="username" placeholder="you@rosebakeshop.com" spellcheck="false" autocapitalize="none">
                </div>
                <label for="password">Password</label>
                <div class="signin-field">
                    <svg aria-hidden="true"><use href="#icon-lock"/></svg>
                    <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Enter your password">
                    <button id="togglePassword" class="password-toggle" type="button" aria-label="Show password" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button>
                </div>
                <button id="loginSubmit" class="button signin-submit" type="submit"><span>Sign in to your storage</span><svg aria-hidden="true"><use href="#icon-arrow"/></svg></button>
            </form>
            <div id="loginStatus" role="status" aria-live="polite"></div>
            @if(config('sso.enabled'))
                <div class="sso-divider"><span>or continue with</span></div>
                <a class="sso-signin button" href="{{ route('sso.redirect') }}"><img src="{{ asset('brand/rose-logo-transparent.png') }}" alt="" width="34" height="25"><span>Sign in with Rose Account</span><svg aria-hidden="true"><use href="#icon-arrow"/></svg></a>
            @endif
            <p class="signin-help">Need access or a password reset?<br><strong>Contact your Rose Storage administrator.</strong></p>
            <div class="signin-rule" aria-hidden="true"></div>
            <p class="signin-footnote"><svg aria-hidden="true"><use href="#icon-lock"/></svg> Your files. Your access. Your peace of mind.</p>
        </div>
    </div>
    <footer class="login-footer">Rose Storage <span aria-hidden="true">·</span> Part of the Rose family</footer>
    @include('partials.brand-waves')
</section>
