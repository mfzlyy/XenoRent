<?php

return [
    'api_url' => env('CURRENCY_RATE_API_URL', 'https://api.frankfurter.dev/v1/latest?base=USD&symbols=IDR'),
    'ca_bundle' => env('CURRENCY_CA_BUNDLE') ?: (ini_get('curl.cainfo') ?: (ini_get('openssl.cafile') ?: env('CURL_CA_BUNDLE'))),
    'cache_minutes' => (int) env('CURRENCY_RATE_CACHE_MINUTES', 10),
];
