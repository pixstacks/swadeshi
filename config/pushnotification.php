<?php
/**
 * config('constants.android_push_key')
 * @see https://github.com/Edujugon/PushNotification
 */

return [
    'gcm' => [
        'priority' => 'high',
        'dry_run'  => false,
        'apiKey'   => config('constants.android_push_key', 'AAAAl4Hyffc:APA91bEGmABGLK7PPIo2epUGNrZic3DMmVHOu8S4kOkar-HfVyuCNIfl82atIGWIU5APhZ2EwPCxb6LKO6SH9HnwDBYTyGZwfqJ6WMx_q0ppQyxUfxQLX_1HJ7YrnCKIbeClOkC9Kojp	
'),
    ],
    'fcm' => [
        'priority' => 'high',
        'dry_run'  => false,
        'apiKey'   => config('constants.android_push_key', 'AAAAl4Hyffc:APA91bEGmABGLK7PPIo2epUGNrZic3DMmVHOu8S4kOkar-HfVyuCNIfl82atIGWIU5APhZ2EwPCxb6LKO6SH9HnwDBYTyGZwfqJ6WMx_q0ppQyxUfxQLX_1HJ7YrnCKIbeClOkC9Kojp	
'),
    ],
    'apn' => [
        'certificate' => __DIR__ . '/iosCertificates/apns-dev-cert.pem',
        'passPhrase'  => 'secret', //Optional
        'passFile'    => __DIR__ . '/iosCertificates/yourKey.pem', //Optional
        'dry_run'     => true,
    ],
    'IOSUser'     => [
        'environment' => 'production',
        'certificate' => storage_path() . '/app/public/apns/user.pem',
        'passPhrase'  => config('constants.ios_push_password'),
        'service'     => 'apns',
    ],
    'IOSProvider' => [
        'environment' => 'production',
        'certificate' => storage_path() . '/app/public/apns/provider.pem',
        'passPhrase'  => config('constants.ios_push_password'),
        'service'     => 'apns',
    ],
    'Android' => [
        'priority' => 'high',
        'dry_run'  => false,
        'apiKey'   => config('constants.android_push_key', 'AAAAl4Hyffc:APA91bEGmABGLK7PPIo2epUGNrZic3DMmVHOu8S4kOkar-HfVyuCNIfl82atIGWIU5APhZ2EwPCxb6LKO6SH9HnwDBYTyGZwfqJ6WMx_q0ppQyxUfxQLX_1HJ7YrnCKIbeClOkC9Kojp	
'),
    ],
];
