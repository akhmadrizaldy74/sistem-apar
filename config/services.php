<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'rajaongkir' => [
        'key' => env('RAJAONGKIR_API_KEY'),
        'base_url' => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),
        'origin_id' => env('RAJAONGKIR_ORIGIN_ID'),
        'couriers' => env('RAJAONGKIR_COURIER', 'jne,jnt,sicepat'),
        'default_weight' => (int) env('RAJAONGKIR_DEFAULT_WEIGHT', 1000),
    ],

    'apar_service_pickup' => [
        'store_lat' => (float) env('APAR_SERVICE_PICKUP_STORE_LAT', -6.457629743293867),
        'store_lng' => (float) env('APAR_SERVICE_PICKUP_STORE_LNG', 106.84730349536345),
        'rate_per_km' => (float) env('APAR_SERVICE_PICKUP_RATE_PER_KM', 3500),
        'min_cost' => (float) env('APAR_SERVICE_PICKUP_MIN_COST', 15000),
    ],

];
