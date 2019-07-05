<?php

namespace Usersettings\Userprofile\Http\Middleware;

use Usersettings\Userprofile\Userprofile;

class Authorize
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        return resolve(Userprofile::class)->authorize($request) ? $next($request) : abort(403);
    }
}
