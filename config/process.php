<?php

/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

return [
    // fileMonitor to check and reload workerman
    'monitor' => [
        'handler'     => process\FileMonitor::class,
        'reloadable'  => false,
        'constructor' => [
            //Directories to monitor
            'monitor_dir' => [
                app_path(),
                config_path(),
                base_path() . '/process',
                base_path() . '/support',
                base_path() . '/resource',
                base_path() . '/.env',
            ],
            // extensions to monitor
            'monitor_extensions' => [
                'php', 'html', 'htm', 'env'
            ]
        ]
    ],
    'AsyncPHPMailer'  => [
        'handler'  => process\AsyncPHPMailer::class,
        'listen' => 'Text://0.0.0.0:12345',
        'count'  => env('MAIL_PROCESS_COUNT', floor(cpu_count() / 4)),
    ]
];
