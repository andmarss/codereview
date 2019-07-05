@extends('nova::auth.layout')

@section('content')

    @include('nova::auth.partials.header')

    <form
        class="bg-white shadow rounded-lg p-8 max-w-login mx-auto"
        method="POST"
        action="{{ route('register') }}"
        enctype="multipart/form-data"
    >
        {{ csrf_field() }}

        @include('nova::auth.partials.navigation', ['left' => [
            'url' => url('/login'),
            'text' => 'Войти в кабинет'
        ], 'right' => [
            'url' => url(route('nova.password.request')),
            'text' => 'Забыли пароль?'
        ]])

        @if ($errors->any())
            <p class="text-center font-semibold text-danger my-3">
                @if ($errors->has('email'))
                    {{ $errors->first('email') }}
                @elseif($errors->has('password'))
                    {{ $errors->first('password') }}
                @elseif($errors->has('createUserError'))
                    {{ $errors->first('createUserError') }}
                @elseif($errors->has('name'))
                    {{ $errors->first('name') }}
                @endif
            </p>
        @endif

        <div class="mb-6 {{ $errors->has('name') ? ' has-error' : '' }}">
            <label class="block font-bold mb-2" for="email">Ваше имя</label>
            <input class="form-control form-input form-input-bordered w-full" placeholder="Ваше имя" id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
        </div>

        <div class="mb-6 {{ $errors->has('email') ? ' has-error' : '' }}">
            <label class="block font-bold mb-2" for="email">Email адрес</label>
            <input class="form-control form-input form-input-bordered w-full" placeholder="Email адрес" id="email" type="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="mb-6 {{ $errors->has('password') ? ' has-error' : '' }}">
            <label class="block font-bold mb-2" for="password">Пароль</label>
            <input class="form-control form-input form-input-bordered w-full" placeholder="Пароль" id="password" type="password" name="password" required>
        </div>

        @if(isset($name) && isset($id))
            <div class="mb-6">
                Вы будете зарегистрированы, как {{$name}}

                <input type="hidden" name="user[name]" value="{{$name}}">
                <input type="hidden" name="user[id]" value="{{$id}}">
            </div>
        @endif

        <button class="w-full btn btn-default btn-primary hover:bg-primary-dark" type="submit">
            Зарегистрироваться
        </button>
    </form>
@endsection

@section('scripts')
    <script src="{{asset('js/app.js')}}"></script>
@endsection
