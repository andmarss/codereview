@extends('nova::auth.layout')

@section('content')

    @include('nova::auth.partials.header')

    <form
        class="bg-white shadow rounded-lg p-8 max-w-login mx-auto"
        method="POST"
        action="{{ route('password.forgot') }}"
    >
        {{ csrf_field() }}

        @include('auth.partials.navigation', ['left' => [
            'url' => url('/login'),
            'text' => 'Войти в кабинет'
        ], 'right' => [
            'url' => url(route('nova.api.register-index')),
            'text' => 'Зарегистрироваться'
        ]])

        @component('nova::auth.partials.heading')
            {{ __('Forgot your password?') }}
        @endcomponent

        @if (session('status'))
            <div class="text-success text-center font-semibold my-3">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="text-center font-semibold text-danger my-3">
                @if ($errors->has('email'))
                    {{ $errors->first('email') }}
                    <p class="mt-3 text-center"><a class="text-primary text-danger font-bold no-underline hover:underline" href='{{url(route('nova.api.register-index'))}}'>Зарегестрироваться</a></p>
                @else
                    {{ $errors->first('password') }}
                @endif
            </div>
        @endif

        <div class="mb-6 {{ $errors->has('email') ? ' has-error' : '' }}">
            <label class="block font-bold mb-2" for="email">{{ __('Email Address') }}</label>
            <input class="form-control form-input form-input-bordered w-full" id="email" type="email" name="email" value="{{ old('email') }}" required>
        </div>

        <button class="w-full btn btn-default btn-primary hover:bg-primary-dark" type="submit">
            Восстановить пароль
        </button>
    </form>
@endsection
