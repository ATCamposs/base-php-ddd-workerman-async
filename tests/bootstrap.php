<?php

require_once  './vendor/autoload.php';
require_once  'support/bootstrap/Container.php';
require_once  'support/bootstrap/Session.php';
require_once  'support/bootstrap/db/Laravel.php';
require_once  'support/bootstrap/Redis.php';
require_once  'support/bootstrap/Log.php';
require_once  'support/bootstrap/Translation.php';
require_once  'support/bootstrap/db/Heartbeat.php';
require_once  'support/DataBase.php';

if ($timezone = config('app.default_timezone')) {
    date_default_timezone_set($timezone);
}

use Workerman\Worker;
use Webman\Config;
use Dotenv\Dotenv;

if (!method_exists('Dotenv\Dotenv', 'createUnsafeImmutable')) {
    Dotenv::createMutable(base_path())->load();
}

if (method_exists('Dotenv\Dotenv', 'createUnsafeImmutable')) {
    Dotenv::createUnsafeImmutable(base_path())->load();
}

Config::load(config_path(), ['route', 'container']);
$config = config('server');

$worker = new Worker($config['listen'], $config['context']);

foreach (config('bootstrap', []) as $class_name) {
    /** @var \Webman\Bootstrap $class_name */
    $class_name::start($worker, $debug = true);
}
