<?php
declare(strict_types=1);




session_start();


define('BASE_PATH', dirname(__DIR__));


define('BASE_URL', '/needink');


require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/config/helpers.php';
require_once BASE_PATH . '/config/auth.php';
