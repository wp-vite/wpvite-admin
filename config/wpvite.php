<?php

return [
    'root_domain' => env('WPVITE_ROOT_DOMAIN', 'wpvite.com'),

    'ssh' => [
        'username'  => env('SSH_USERNAME'),
        'private_key_path'  => env('SSH_PRIVATE_KEY_PATH'),
    ]
];
