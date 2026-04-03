<?php

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
            'cookie' => env('ADMIN_SESSION_COOKIE', 'admin_session'),
        ],
    
        'masters' => [
            'driver' => 'session',
            'provider' => 'masters',
            'cookie' => env('MASTERS_SESSION_COOKIE', 'masters_session'),
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin\Admin::class,
        ],
        
        'masters' => [
            'driver' => 'eloquent',
            'model' => App\Models\Masters\User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
        
        'admins' => [
            'provider' => 'admins',
            'table' => 'admin_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        
        'masters' => [
            'provider' => 'masters',
            'table' => 'masters_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];