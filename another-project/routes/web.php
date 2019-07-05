<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// регистрация
Route::get('/register', 'RegisterController@index')->name('register.index');
Route::post('/register', 'RegisterController@register')->name('register');
// подтверждение регистрации
Route::get('/login/verify/{token}', 'RegisterController@showVerifyPage')->name('verify.page');
Route::get('/verify/{token}', 'RegisterController@verify')->name('verify');
Route::post('/login', 'Auth\LoginController@authLogin')->name('login');
// получение информации о пользователе
Route::get('/user-data','PersonalAccountController@getData');
// переход на следующее состояние, если это возможно
Route::post('/transition', 'PersonalAccountController@transition');
// получение превьюхи загруженного изображения
Route::get('/user/{user_id}/file/{id}', 'FilesController@file');
// загрузка файла пользователем
Route::post('/user/{id}/file_uploading','FilesController@uploadUserFile');
// удаление файла пользователем
Route::post('/user/{user_id}/file/{id}/remove', 'FilesController@removeFile');
// страница специалиста
Route::middleware('specialist.check')->prefix('specialist')->group(function (){
    // получение информации о пользователе на странице доктора
    Route::post('/information/{id}', 'PersonalAccountController@specialist');
    // Получить список отзывов
    Route::post('/reviews/{id}', 'PersonalAccountController@reviews');
    // Ответ на отзыв на странице специалиста
    Route::post('/{id}/review/{review_id}/answer', 'PersonalAccountController@review');
    // пользователь нажал "Отправить на модерацию"
    Route::post('/{id}/moderation', 'PersonalAccountController@moderation');
    // пользователь загрузил фото
    Route::post('/{id}/upload-photo', 'PersonalAccountController@uploadSpecialistPhoto');
    // пользователь нажал "Удалить профиль"
    Route::post('/{id}/profile/action', 'PersonalAccountController@action');
    // пользователь нажал "сохранить"
    Route::post('/{id}/update', 'PersonalAccountController@update');
});

Route::middleware('check.moderator')->prefix('api')->group(function (){
    Route::post('/users', 'ModeratorController@users');
    Route::post('/user/{id}', 'ModeratorController@user');
    Route::post('/transition', 'ModeratorController@transition');
    Route::post('/information/specialists', 'ModeratorController@getSpecialists');
    Route::post('/moderate', 'ModeratorController@moderate');
    Route::post('/specialist/{id}/reject', 'ModeratorController@reject');
});
