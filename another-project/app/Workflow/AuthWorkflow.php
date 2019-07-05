<?php

namespace App\Workflow;


class AuthWorkflow
{
    const WORKFLOW_NAME = 'auth';
    /**
     * Статусы для процесса авторизации и дальнейших манипуляций с профилем
     */
    const PLACE_AUTH_START = 'start'; // пользователь регистрируется на сайте первый раз
    const PLACE_AUTH_REGISTERED = 'registered'; // пользователь зарегистрирован
    const PLACE_AUTH_CONFIRMED = 'confirmed'; // статус подтвержден пользователем
    const PLACE_AUTH_MODERATOR_CONFIRMED = 'moder_confirmed'; // модератор подтвердил статус пользователя
    const PLACE_AUTH_CHANGED = 'changed'; // пользователь внес изменения
    const PLACE_AUTH_CHANGE_CONFIRMED = 'change_confirmed'; // внесенные пользователем изменения подтверждены модератором

    const PLACE_AUTH_CHANGE_RETURNED = 'change_returned'; // отказ в изменении
    const PLACE_AUTH_REGISTER_RETURNED = 'register_returned'; // отказ в подтверждении регистрации

    const PLACE_DELETE_ACCOUNT = 'deleted'; // пользователь изъявил желание удалить профиль

    const TRANSITION_AUTH_REGISTER = 'register';
    const TRANSITION_AUTH_CONFIRM = 'confirm';
    const TRANSITION_AUTH_MODERATOR_CONFIRM = 'moder_confirm';
    const TRANSITION_AUTH_DATA_CHANGE = 'change';
    const TRANSITION_AUTH_DATA_CHANGE_CONFIRM = 'change_confirm';

    const TRANSITION_AUTH_BACK_TO_CHANGE = 'change_return';
    const TRANSITION_AUTH_BACK_TO_REGISTER = 'register_return';

    const TRANSITION_DELETE = 'delete';

    /**
     * Массив этапов
     *
     * @return array
     */
    public static function getPlaces(): array
    {
       return [
           self::PLACE_AUTH_START,
           self::PLACE_AUTH_REGISTERED,
           self::PLACE_AUTH_CONFIRMED,
           self::PLACE_AUTH_MODERATOR_CONFIRMED,
           self::PLACE_AUTH_CHANGED,
           self::PLACE_AUTH_CHANGE_CONFIRMED,
           self::PLACE_DELETE_ACCOUNT,
           self::PLACE_AUTH_CHANGE_RETURNED,
           self::PLACE_AUTH_REGISTER_RETURNED
       ];
    }
}
