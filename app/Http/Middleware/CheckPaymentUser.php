<?php
/**
 * Created by PhpStorm.
 * User: nikolay
 * Date: 27.12.18
 * Time: 13:24
 */

namespace App\Http\Middleware;

use App\Models\Payment;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Nova;

class CheckPaymentUser
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
        // Получаем пользователя
        /**
         * @var User $user
         */
        $user = Auth::user();
        /**
         * @var Payment $payment
         */
        $payment = Payment::query()->where('id', $request->id)->first();

        if($payment && $user && $payment->account_id === $user->getAccount()->id) {
            $request->attributes->add(['payment' => $payment]);
            return $next($request);
        }

        return redirect(Nova::path());
    }
}
