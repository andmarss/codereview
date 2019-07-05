<?php

namespace App\Http\Middleware;

use App\User;
use Closure;

class CheckSpecialist
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
        $user = User::find($request->route('id'));
        /**
         * @var User $authorized
         */
        $authorized = auth()->user();
        // разрешаем получать информацию только о себе
        // если запрашивающий - модератор, то может смотреть любого
        if($user && (($user->id === $authorized->id && $user->isDoctor()) || $authorized->isModerator())) {
            $request->attributes->add(['user' => $user]);

            return $next($request);
        }

        return response()->json(['access' => false]);
    }
}
