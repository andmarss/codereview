@extends('nova::auth.layout')

@section('content')
    @include('nova::auth.partials.header')

    <div class="bg-white shadow rounded-lg p-8 mx-auto lg:w-1/2 md:w-full sm:w-full">
        @include('nova::auth.partials.navigation', ['left' => [
            'url' => url('/login'),
            'text' => 'Войти в кабинет'
        ]])

        <p class="mb-3 text-base">На адрес Вашей электронной почты <span class="text-blue">{{$email}}</span> было отправлено письмо с подтверждением регистрации.</p>
        <p class="text-base">Перейдите по полученной ссылке, что бы завершить регистрацию.</p>
    </div>
@stop
