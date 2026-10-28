<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BackupAdministrator
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(in_array((int) $request->user()->id, config('backups.admin_user_ids'), true), 403);
        // Narrowly scoped application tokens cannot export the entire installation.
        abort_unless(in_array('*', $request->user()->currentAccessToken()?->abilities ?? [], true), 403);

        return $next($request);
    }
}
