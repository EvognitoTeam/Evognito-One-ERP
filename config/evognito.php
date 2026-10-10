<?php

return [

    'superadmin' => [

        'name' => env(
            'EVOGNITO_SUPERADMIN_NAME',
            'Evognito Owner'
        ),

        'email' => env(
            'EVOGNITO_SUPERADMIN_EMAIL'
        ),

        'username' => env(
            'EVOGNITO_SUPERADMIN_USERNAME',
            'superadmin'
        ),

        'password' => env(
            'EVOGNITO_SUPERADMIN_PASSWORD'
        ),

    ],

];
