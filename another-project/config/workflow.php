<?php

use \App\Workflow\AuthWorkflow;

return [
    'auth' => [
        'type'          => 'state_machine',
        'marking_store' => [
            'type' => 'multiple_state',
            'arguments' => ['places']
        ],
        'supports'      => [App\User::class],
        'places'        => AuthWorkflow::getPlaces(),
        'transitions'   => [
            AuthWorkflow::TRANSITION_AUTH_REGISTER => [
                'from' => [
                    AuthWorkflow::PLACE_AUTH_START,
                    AuthWorkflow::PLACE_DELETE_ACCOUNT,
                    AuthWorkflow::PLACE_AUTH_CONFIRMED
                ],
                'to' => AuthWorkflow::PLACE_AUTH_REGISTERED
            ],
            AuthWorkflow::TRANSITION_AUTH_CONFIRM => [
                'from' => [
                    AuthWorkflow::PLACE_AUTH_REGISTERED,
                    AuthWorkflow::PLACE_DELETE_ACCOUNT,
                    AuthWorkflow::PLACE_AUTH_REGISTER_RETURNED
                ],
                'to' => AuthWorkflow::PLACE_AUTH_CONFIRMED
            ],
            AuthWorkflow::TRANSITION_AUTH_BACK_TO_REGISTER => [
                'from' => [
                    AuthWorkflow::PLACE_AUTH_REGISTERED,
                    AuthWorkflow::PLACE_AUTH_CONFIRMED
                ],
                'to' => AuthWorkflow::PLACE_AUTH_REGISTER_RETURNED
            ],
            AuthWorkflow::TRANSITION_AUTH_MODERATOR_CONFIRM => [
                'from' => [
                    AuthWorkflow::PLACE_AUTH_CONFIRMED,
                    AuthWorkflow::PLACE_DELETE_ACCOUNT
                ],
                'to' => AuthWorkflow::PLACE_AUTH_MODERATOR_CONFIRMED
            ],
            AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE => [
                // пользователь может вносить изменения только если его статус регистрации был подтвержден
                // или если предыдущие изменения были подтверждены модератором
                'from' => [
                    AuthWorkflow::PLACE_AUTH_MODERATOR_CONFIRMED,
                    AuthWorkflow::PLACE_AUTH_CHANGE_CONFIRMED,
                    AuthWorkflow::PLACE_AUTH_CHANGE_RETURNED,
                    AuthWorkflow::PLACE_DELETE_ACCOUNT
                ],
                'to' => AuthWorkflow::PLACE_AUTH_CHANGED
            ],
            AuthWorkflow::TRANSITION_AUTH_BACK_TO_CHANGE => [
                'from' => [
                    AuthWorkflow::PLACE_AUTH_MODERATOR_CONFIRMED,
                    AuthWorkflow::PLACE_AUTH_CHANGE_CONFIRMED,
                    AuthWorkflow::PLACE_AUTH_CHANGED,
                    AuthWorkflow::PLACE_DELETE_ACCOUNT
                ],
                'to' => AuthWorkflow::PLACE_AUTH_CHANGE_RETURNED
            ],
            AuthWorkflow::TRANSITION_AUTH_DATA_CHANGE_CONFIRM => [
                'from' => [
                    AuthWorkflow::PLACE_AUTH_CHANGED,
                    AuthWorkflow::PLACE_DELETE_ACCOUNT
                ],
                'to' => AuthWorkflow::PLACE_AUTH_CHANGE_CONFIRMED
            ],
            AuthWorkflow::TRANSITION_DELETE => [
                'from' => [
                    AuthWorkflow::PLACE_AUTH_MODERATOR_CONFIRMED,
                    AuthWorkflow::PLACE_AUTH_CHANGED,
                    AuthWorkflow::PLACE_AUTH_CHANGE_CONFIRMED,
                    AuthWorkflow::PLACE_AUTH_REGISTER_RETURNED,
                    AuthWorkflow::PLACE_AUTH_CHANGE_RETURNED
                ],
                'to' => AuthWorkflow::PLACE_DELETE_ACCOUNT
            ]
        ]
    ]
];
