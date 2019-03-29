<?php

namespace App\Http\Controllers;

use App\Jobs\SyncBitrixContact;
use App\Models\ClientType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Laravel\Nova\Http\Controllers\LoginController;
use Laravel\Nova\Nova;

class RegisterController extends Controller
{
    public function index()
    {
        return view('nova::auth.register');
    }

    public function register(Request $request)
    {
        // правила для валидации
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ];

        $validator = Validator::make($request->all(), $rules);

        // проверяем валидатор на наличие ошибок
        if($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        try {
            /**
             * @var User $user
             */
            $user = User::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'password' => bcrypt( $request->get('password') ),
                'client_type_id' => ClientType::CLIENT_SIMPLE
            ]);

            SyncBitrixContact::dispatch($user->id);

        } catch (\Exception $e) {
            Log::error(
                "Произошла ошибка при регистрации пользователя." . PHP_EOL .
                "Текст ошибки: " . $e->getMessage() . PHP_EOL .
                "Код ошибки: " . $e->getCode()
            );

            return redirect()->back()->withErrors(['createUserError' => 'Произошла ошибка при выполнении запроса. Повторите попытку еще раз.']);
        }

        if($user) {
            return (new LoginController)->login($request);
        } else {
            return redirect()->back()->withErrors(['createUserError' => 'Произошла ошибка при выполнении запроса. Повторите попытку еще раз.']);
        }
    }

    /**
     * Получает токен
     * По нему проверяет, существует ли пользователь с таким токеном
     * Если да - логинит его, и редиректит на главную страницу
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function check(Request $request)
    {
        /**
         * @var User $user
         */
        $user = User::where(['bd_token' => $request->route( 'bd_token' )])->first();
        // Если найден - авторизируем, и редиректим на главную страницу кабинета
        if($user) {
            Auth::login($user);

            Session::put('super_user', true);

            return redirect(Nova::path());
        } else {
            // Если нет - редиректим на страницу авторизации
            return redirect()->route('nova.login')
                ->withErrors(['invalidToken' => 'Пользователь с указанным токеном не найден']);
        }
    }
}
