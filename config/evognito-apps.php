<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Internal Applications
    |--------------------------------------------------------------------------
    |
    | Module yang hidup langsung di Evognito One modular monolith.
    |
    */

    'internal' => [

        'hris' => [
            'name' => 'HRIS',

            'route' =>
            'hris.dashboard',
        ],

        'sales_force' => [
            'route' => 'sales.dashboard',
            'theme' => 'teal',
        ],

        'performance' => [
            'name' => 'Performance',
            'route' => 'performance.dashboard',
            'theme' => 'teal',
        ],

        'sites' => [
            'name' => 'Sites',
            'route' => 'sites.dashboard',
            'theme' => 'teal',
        ],

        /*
        |--------------------------------------------------------------------------
        | Berikutnya
        |--------------------------------------------------------------------------
        |
        | 'performance' => [
        |     'name' => 'Performance',
        |     'route' => 'performance.dashboard',
        | ],
        |
        | 'sales_force' => [
        |     'name' => 'Sales Force',
        |     'route' => 'sales.dashboard',
        | ],
        |
        */

    ],

];
