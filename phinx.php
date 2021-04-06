<?php

use support\DataBase;

$env = parse_ini_file('.env', true);

    return
    [
        'paths' => [
            'migrations' => 'Database/Migrations',
            'seeds' => 'Database/Seeds'
        ],
        'environments' => [
            'default_migration_table' => 'phinxlog',
            'default_environment' => 'dev',
            'dev' => [
                'adapter' => $env['DB_CONNECTION'],
                'host' => $env['DB_HOST'],
                'name' => $env['DB_DATABASE'],
                'user' => $env['DB_USERNAME'],
                'pass' => $env['DB_PASSWORD'],
                'port' => $env['DB_PORT'],
                'charset' => 'utf8'
            ]
        ],
        'version_order' => 'creation'
    ];
