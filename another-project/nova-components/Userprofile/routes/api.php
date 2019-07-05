<?php

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

 Route::get('/', function () {
     /**
      * @var User $user
      */
     $user = \auth()->user();

     return response()->json(
         [
             'name' => $user->name,
             'email' => $user->email
         ]
     );
 });

/**
 * Нажал "сохранить"
 */
Route::post('/', function (Request $request){
    /**
     * @var User $user
     */
    $user = auth()->user();

    $request->validate([
        'name' => 'required|string',
        'email' => 'required|email',
        'password' => 'nullable|string|confirmed'
    ]);

    if(!is_null($request->get('password')) && $request->get('password') === $request->get('password_confirmation')) {
        $user->update([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password')),
        ]);
    } else {
        $user->update($request->only('name', 'email'));
    }

    $user->save();

    return response()->json(__("Ваш профиль был обновлен!"));
});
