<?php

namespace App\Http\Middleware;

use App\Models\Deal;
use Closure;

class CheckDealUserMiddleware
{
    /**
     * Принимает запрос, если id сделки совдапает с id пользователя - пускаем его
     * Иначе - редиректим
     *
     * @param $request
     * @param Closure $next
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    public function handle($request, Closure $next)
    {
        /**
         * @var Deal $deal
         */
        $deal = Deal::find($request->id);
        /**
         * @var User $user
         */
        $user = auth()->user();

        if($deal && $user && $deal->user_id === $user->id) {
            $request->attributes->add(['deal' => $deal]);
            return $next($request);
        }

        return response()->json(['access' => false]);
    }
}
