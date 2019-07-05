<?php

namespace App\Http\Middleware;

use App\User;
use Closure;

class CheckModerator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /**
         * @var User $user
         */
        $user = auth()->user();

        if($user && $user->isModerator()) {
            return $next($request);
        }

        return response()->json(['access' => false]);
    }
}
