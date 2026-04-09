<?php

return [
    'pass_type_id' => env('APPLE_WALLET_PASS_TYPE_ID', ''),
    'team_id'      => env('APPLE_WALLET_TEAM_ID', ''),
    'cert_path'    => env('APPLE_WALLET_CERT_PATH', storage_path('app/wallet/pass.crt')),
    'key_path'     => env('APPLE_WALLET_KEY_PATH', storage_path('app/wallet/pass.key')),
    'wwdr_path'    => env('APPLE_WALLET_WWDR_PATH', storage_path('app/wallet/AppleWWDRCA.pem')),
    'key_password' => env('APPLE_WALLET_KEY_PASSWORD', ''),
];
