<?php

return [
    'local' => [
        'assets_path' => 'storage/app/public',
    ],
    'remote' => [
        'staging' => [
            'ssh_user' => 'forge',
            'ssh_host' => '1.2.3.5',
            'ssh_path' => 'home/forge/your-staging-domain.com',
            'assets_path' => 'storage/app/public',
        ],
        'production' => [
            'ssh_user' => 'forge',
            'ssh_host' => '1.2.3.5',
            'ssh_path' => 'home/forge/your-production-domain.com',
            'assets_path' => 'storage/app/public',
        ],
    ],
];
