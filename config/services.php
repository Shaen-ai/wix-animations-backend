<?php

return [
    'wix' => [
        'app_id' => env('WIX_APP_ID', ''),
        'app_secret' => env('WIX_APP_SECRET', ''),
        'public_key' => env('WIX_PUBLIC_KEY', ''),
    ],
    // Site Notice Banner - same credential pattern as wix-payment-backend
    'site_notice_banner' => [
        'app_id' => env('SITE_NOTICE_BANNER_APP_ID', '0d076a26-ce6d-4d16-83c5-126cdf640aa4'),
        'app_secret' => env('SITE_NOTICE_BANNER_APP_SECRET', 'b2c6993d-738d-48ca-97a5-1d51e330def4'),
    ],
];
