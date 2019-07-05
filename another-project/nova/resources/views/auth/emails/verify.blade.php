@component('mail::message')

# Привет!

Вы были зарегистрированы на нашем сайте [{{ config('app.name') }}]({!! url('/') !!}).

@component('mail::button', ['url' => $url, 'color' => 'green'])
    Подтвердить E-mail
@endcomponent

Если вы нигде не регистрировались, никаких дополнительный действий не требуется.

С уважением, <br/>
{{ config('app.name') }}

@component('mail::subcopy')
    Если у вас возникли проблемы с нажатием кнопки «Подтвердить E-mail», скопируйте и вставьте приведенный ниже URL-адрес в адресную строку вашего браузера.
    [{{ $url }}]({!! $url !!})
@endcomponent

@endcomponent
