<?php

namespace Usersettings\Userslist\Http\Middleware;

use Usersettings\Userslist\Userslist;

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
        return resolve(Userslist::class)->authorize($request) ? $next($request) : abort(403);
    }
}
