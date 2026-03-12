<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | Use the custom 'apobox' hasher that supports legacy MD5+salt passwords
    | from the CakePHP 2.x era and transparently upgrades to bcrypt.
    |
    */

    'driver' => 'apobox',

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12),
        'verify' => true,
    ],

    'argon' => [
        'memory' => 65536,
        'threads' => 1,
        'time' => 4,
        'verify' => true,
    ],

    'argon2id' => [
        'memory' => 65536,
        'threads' => 1,
        'time' => 4,
        'verify' => true,
    ],

    'rehash_on_login' => true,

];
