<?php

use Illuminate\Database\Capsule\Manager as Capsule;

// General Configurations
define('BOT_TOKEN', '');
define('DEV_TG_ID', 1234567);

// Game Configurations
define('FREE_HINTS', 2);
define('VIP_HINTS', 6);
define('MAX_WRONG', 7);
define('LEVEL_SCORE', 2);
define('MISTAKE_DUES', 0.3);

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => '',
    'username'  => '',
    'password'  => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();