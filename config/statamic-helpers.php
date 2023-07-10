<?php

return [
    'with_shop_addon' => false,
    'preset_on_upload' => true,
    'preset_disk' =>'presets',
    'presets' => [
        'xs' => ['w' => 100, 'h' => 100, 'q' => 10, 'fit' => 'max'],
        'sm' => ['w' => 300, 'h' => 300, 'q' => 10, 'fit' => 'max'],
        'base' => ['w' => 800, 'h' => 800, 'q' => 10, 'fit' => 'max'],
        'lg' => ['w' => 1200, 'h' => 1200, 'q' => 10, 'fit' => 'max'],
        'xl' => ['w' => 1600, 'h' => 1600, 'q' => 10, 'fit' => 'max'],
    ],
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
