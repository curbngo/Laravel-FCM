<?php

return [
    'driver' => env('FCM_PROTOCOL', 'http'),
    'log_enabled' => false,

    'http' => [
        'server_key' => env('FCM_SERVER_KEY', 'Your FCM server key'),
        'sender_id' => env('FCM_SENDER_ID', 'Your sender id'),
        'server_send_url' => 'https://fcm.googleapis.com/v1/projects/' . env('FCM_PROJECT_ID') . '/messages:send',
        'fcm_admin_sdk_file' => env('FCM_ADMIN_SDK_JSON'),
        'fcm_cache_access_key' => env('FCM_CACHE_ACCESS_KEY'),
        'timeout' => 30.0, // in second
    ],
];
