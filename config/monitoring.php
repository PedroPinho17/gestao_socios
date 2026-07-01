<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Healthchecks.io
    |--------------------------------------------------------------------------
    |
    | URLs de ping (https://healthchecks.io) para confirmar que tarefas
    | agendadas correram. Deixe vazio em local/dev se não usar monitoring.
    |
    */

    'healthchecks' => [
        'quota_reminders' => env('HEALTHCHECKS_QUOTA_REMINDERS_URL'),
    ],

];
