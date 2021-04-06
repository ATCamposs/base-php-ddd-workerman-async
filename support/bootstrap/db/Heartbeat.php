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

namespace support\bootstrap\db;

use Webman\Bootstrap;
use support\DataBase;

/**
 * * mysql heartbeat. Send a query regularly to prevent the mysql connection from being disconnected by the mysql
 * server when the mysql connection is inactive for a long time.
 * It is not turned on by default, if you need to turn it on, please add
 * support\bootstrap\db\Heartbeat::class in config/bootstrap.php,
 * @package support\bootstrap\db
 */
class Heartbeat implements Bootstrap
{
    /**
     * @param \Workerman\Worker $worker
     *
     * @return void
     */
    public static function start($worker)
    {
        \Workerman\Timer::add(55, function () {
            DataBase::select('select 1 limit 1');
        });
    }
}
