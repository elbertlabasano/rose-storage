# Sign in with Rose Account

Rose Storage uses Keycloak realm `jathnier` and client `storage` at
`https://sso.rosebakeshop.net`. The browser flow uses state and PKCE S256, then
the backend exchanges the authorization code using `client_secret_post` and
retrieves UserInfo through verified HTTPS. Provider tokens and the client secret
never go into browser URLs or browser storage.

## Existing users only

Each local user needs a unique string `idcode`. No account is created by SSO and
email addresses are not used to match users. Assign an employee code:

```sh
php artisan migrate
php artisan sso:link admin@example.com 20260211
```

The first successful sign-in binds the configured issuer and stable Keycloak
subject to that account. Leading zeros in `idcode` are preserved. Missing codes,
unknown codes, inactive users, changed codes, and conflicting identity links are
denied. Linked identity changes require administrator review; the command will
not replace an existing identity with a different employee code. Local user IDs,
bucket ownership, and backup administrator allowlists remain unchanged.

In Keycloak, the `storage` client's UserInfo mapper must include `idcode` as one
string from the employee's managed user attribute. Make the attribute editable
by administrators only, and ensure its value matches the existing storage user.

## Local settings

```dotenv
SSO_ENABLED=true
KEYCLOAK_BASE_URL=https://sso.rosebakeshop.net
KEYCLOAK_REALM=jathnier
KEYCLOAK_CLIENT_ID=storage
KEYCLOAK_CLIENT_SECRET=your-private-client-secret
KEYCLOAK_REDIRECT_URI=http://127.0.0.1:8007/auth/keycloak/callback
SSO_IDCODE_CLAIM=idcode
SSO_TOKEN_LIFETIME_MINUTES=120
SESSION_COOKIE=rose_storage_session
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax
```

The local PHP development server uses **HTTP**, so register that exact HTTP
callback in Keycloak. `https://127.0.0.1:8007/...` cannot work against a plain
HTTP server. Use the same hostname to open the app and receive the callback so
its state cookie is present. Do not mix `localhost` and `127.0.0.1` during a login.

For production, register and configure the exact callback
`https://storage.rosebakeshop.net/auth/keycloak/callback`, set `APP_URL` to that
HTTPS origin, and set `SESSION_SECURE_COOKIE=true`. Use host-only cookies and
configure trusted proxies only for the actual deployment. Leave provider TLS
verification enabled. In Keycloak enable client authentication and Standard flow,
disable implicit/direct/service-account flows unless separately needed, and enforce
PKCE S256. Browser web origins and central logout redirects are not required here.

After editing environment settings:

```sh
php artisan config:clear
php artisan route:clear
php artisan route:list --path=auth/keycloak
```

## Dashboard and logout

SSO creates a regenerated, HTTP-only local web session. The dashboard restores it
through a CSRF-protected POST and obtains its normal, short-lived Sanctum browser
token. Only its token ID is kept in the server session; the plain token is returned
in a no-store JSON response and kept in sessionStorage, matching the existing
dashboard's authentication model. Reloading replaces that web session's previous
browser token; integration tokens are not revoked. Browser tabs sharing the same
SSO session may need to reload after another tab restores the session.

Sign out revokes the local SSO browser token and invalidates the local web session.
It does not call Keycloak logout, revoke the shared Rose session, or sign users
out of other applications. Password login stays available for recovery.

Central Keycloak disable/logout does not immediately revoke an existing local
token or web session. Backchannel logout and continuous central revalidation are
not implemented. Browser tokens expire after the configured lifetime (1–480
minutes); local inactive accounts cannot establish/restore an SSO session. Review
the desired central revocation policy before a wider deployment.

Set `SSO_ENABLED=false` to hide the entry point and reject new SSO callbacks.
Existing sessions/tokens require separate revocation if needed. Keep `.env`
private and outside source control; never log credentials or upstream tokens.
