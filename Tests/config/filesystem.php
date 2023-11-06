<?php

return [
    'disks' => [
        /*
        |--------------------------------------------------------------------------
        | Example config for Local FileSystem.
        |--------------------------------------------------------------------------
        */
        'local' => [
            /*
            |--------------------------------------------------------------------------
            | Set the root directory.
            |--------------------------------------------------------------------------
            */
            'root' => (string) __DIR__ . '/../storage/logs',
            /*
            |--------------------------------------------------------------------------
            | Set the visibility for files and directories.
            |--------------------------------------------------------------------------
            */
            'visibility' => \League\Flysystem\Visibility::PRIVATE,
            'permission' => [
                'file' => [
                    'public'  => (int) 0644,
                    'private' => (int) 0604,
                ],
                'dir'  => [
                    'public'  => (int) 0755,
                    'private' => (int) 7604,
                ],
            ],
        ],
    ],
];
