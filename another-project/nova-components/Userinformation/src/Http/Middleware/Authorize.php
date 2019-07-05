<?php

namespace Usersettings\Userinformation\Http\Middleware;

use Usersettings\Userinformation\Userinformation;

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
        return resolve(Userinformation::class)->authorize($request) ? $next($request) : abort(403);
    }
}
