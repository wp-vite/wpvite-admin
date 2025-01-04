<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AWS Default Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines the default AWS credentials, region, and other
    | settings that are commonly used across your application.
    |
    */

    'access_key'    => env('AWS_ACCESS_KEY_ID'),
    'secret_key'    => env('AWS_SECRET_ACCESS_KEY'),

    'region' => env('AWS_DEFAULT_REGION', 'ap-south-1'),

    /*
    |--------------------------------------------------------------------------
    | AWS Service-Specific Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for individual AWS services. You can customize settings
    | for each service, such as S3, DynamoDB, or Systems Manager.
    |
    */

    'ssm' => [
        'parameter_prefix' => env('AWS_SSM_PARAMETER_PREFIX', '/wpvite/'), // Optional prefix for Parameter Store
        'kms_key_id' => env('AWS_SSM_KMS_KEY_ID'), // KMS key for encryption
    ],

    /*
    |--------------------------------------------------------------------------
    | Additional Settings
    |--------------------------------------------------------------------------
    |
    | Here you can define any additional AWS-related settings that your
    | application may need, such as default timeouts or logging options.
    |
    */

    'settings' => [
        'version' => env('AWS_SDK_VERSION', 'latest'), // SDK version
        'debug'   => env('AWS_DEBUG', false), // Debugging mode
    ],

];
