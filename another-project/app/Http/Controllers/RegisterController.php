<?php

namespace App\Http\Controllers;

use App\Mail\VerifyMail;
use App\User;
use App\VerifyUser;
use App\Workflow\AuthWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Laravel\Nova\Nova;
use Silvanite\Brandenburg\Role;

class RegisterController extends Controller
{
    public function index(Request $request)
    {
        return view('nova::auth.register')->with([
            'id' => $request->get('id'),
            'name' => $request->get('name')
        ]);
    }

    public function register(Request $request)
    {
        // правила для валидации
        $rules = [
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ];

        $validator = Validator::make($request->all(), $rules);

        // проверяем валидатор на наличие ошибок
        if($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        /**
         * @var User $user
         */
        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => bcrypt( $request->get('password') )
        ]);

        if($user) {

            /**
             * Ищем роль "пользователь НЕ модератор"
             * @var Role $role
             */
            $role = Role::where(['id' => User::ROLE_USER])->first();

            if ($role) {
                // проставляем пользователю роль простого пользователя
                $user->roles()->attach($role);

                $user->save();
            }

            if(!is_null($request->get('user'))) {
                /**
                 * @var array $userData
                 */
                $userData = $request->get('user');

                if(count($userData) > 0) {
                    collect($userData)
                        ->each(function (string $value, string $key) use (&$user) {
                            $user->setParam($key, $value);
                        });

                    $user->save();
                }
            }
            /**
             * @var VerifyUser $verifyUser
             */
            $verifyUser = VerifyUser::create([
                'user_id' => $user->id,
                'token' => str_random(40)
            ]);
            // отправляем пользователю на почту ссылку с подтверждением
            Mail::to($user->email)->send(new VerifyMail($user));

            return redirect()
                ->route('verify.page', [
                        'token' => $verifyUser->token
                    ]);
        } else {
            return redirect()->back()->withErrors(['createUserError' => 'Произошла ошибка при выполнении запроса. Повторите попытку еще раз.']);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View|string
     */
    public function showVerifyPage(Request $request)
    {
        /**
         * @var VerifyUser $verify
         */
        $verify = VerifyUser::where(['token' => $request->route('token')])->first();

        if(!$verify) {
            Session::flash('error', 'Указанный токен не найден');

            return back();
        }
        /**
         * @var User $user
         */
        $user = $verify->user;

        if(!$user) {
            Session::flash('error', 'Пользователь не найден');

            return back();
        }

        if($user->isVerified()) {
            Session::flash('info', 'Пользователь уже зарегистрирован');

            return redirect(Nova::path());
        }

        return view('nova::auth.verify')->with([
            'email' => $user->email
        ]);
    }

    public function verify(Request $request)
    {
        /**
         * @var VerifyUser $verify
         */
        $verify = VerifyUser::where(['token' => $request->route('token')])->first();

        if(!$verify) {
            Session::flash('error', 'Указанный токен не найден');

            return redirect(Nova::path());
        }
        /**
         * @var User $user
         */
        $user = $verify->user;

        if(!$user) {
            Session::flash('error', 'Пользователь не найден');

            return redirect(Nova::path());
        }

        if($user->isVerified()) {
            Session::flash('info', 'Пользователь уже зарегистрирован');

            return redirect(Nova::path());
        }

        $user->verified = true;
        // переводим пользователя в состояние регистрации
        $user->workflow_apply(AuthWorkflow::TRANSITION_AUTH_REGISTER, 'auth');

        $user->save();

        Session::flash('status', 'Поздравляем, Вы успешно зарегистрированы');

        return redirect(url('/login'));
    }
}
