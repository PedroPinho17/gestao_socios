<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Exportação profissional (Browsershot / Chrome headless)
    |--------------------------------------------------------------------------
    |
    | Quando Chrome + Node estão disponíveis, PDF e PNG usam o mesmo HTML/CSS
    | do preview no browser (qualidade gráfica). Sem isto, usa DomPDF/GD.
    |
    */

    'export' => [
        'chrome_path' => env('BROWSERSHOT_CHROME_PATH'),
        'node_binary' => env('BROWSERSHOT_NODE_BINARY'),
        'npm_binary' => env('BROWSERSHOT_NPM_BINARY'),
        'node_modules_path' => env('BROWSERSHOT_NODE_MODULES_PATH'),
        'no_sandbox' => env('BROWSERSHOT_NO_SANDBOX', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Impressoras Evolis (fase futura)
    |--------------------------------------------------------------------------
    |
    | Os PNG/PDF CR80 gerados podem ser enviados manualmente ao driver Evolis.
    | Integração directa (SDK/USB) ficará para uma fase posterior.
    |
    */

    'evolis' => [
        'enabled' => env('EVOLIS_ENABLED', false),
        'printer_name' => env('EVOLIS_PRINTER_NAME'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validade do link QR de validação (meses)
    |--------------------------------------------------------------------------
    */

    'validation_link_ttl_months' => (int) env('MEMBER_CARD_QR_TTL_MONTHS', 12),

];
