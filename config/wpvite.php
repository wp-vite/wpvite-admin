<?php

return [
    // Admin Email
    'admin_email'   => env('ADMIN_EMAIL', 'support@wpvite.com'),

    // Root domain
    'root_domain' => env('WPVITE_ROOT_DOMAIN', 'wpvite.com'),

    // Master SSH credentials for hosting servers
    'ssh' => [
        'username'  => env('SSH_USERNAME'),
        'private_key_path'  => env('SSH_PRIVATE_KEY_PATH'),
    ]
];
