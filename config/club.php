<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Logótipo do backend (painel, login, validação QR, área do sócio)
    |--------------------------------------------------------------------------
    |
    | Caminho relativo à pasta public/ (ex.: img/vale_logo.png).
    | O logótipo do cartão continua a ser carregado em Definições do clube.
    |
    */

    'logo' => env('CLUB_LOGO', 'img/vale_logo.png'),

    /*
    |--------------------------------------------------------------------------
    | Textos da área do sócio (frontend React)
    |--------------------------------------------------------------------------
    */

    'member_area' => [
        'title' => env('CLUB_MEMBER_AREA_TITLE', 'Área do sócio'),
        'login_subtitle' => env(
            'CLUB_MEMBER_AREA_LOGIN_SUBTITLE',
            'Inicie sessão com o email e password do clube.',
        ),
    ],

];
