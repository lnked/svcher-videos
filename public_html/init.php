<?php declare(strict_types = 1);

error_reporting(E_ALL | E_STRICT);

date_default_timezone_set('Europe/Moscow');

define('LOCAL_SERVER', in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1')));
define('DEV_MODE', LOCAL_SERVER);

define('DS', DIRECTORY_SEPARATOR);
define('PATH_ROOT', __DIR__);
define('PATH_CORE', PATH_ROOT.DS.'core');
define('PATH_VENDORS', PATH_CORE.DS.'vendors');
define('PATH_TEMPLATE', PATH_CORE.DS.'template');

define('DB_HOST', 'localhost');
define('DB_BASE', 'video');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_PORT', '3306');

ini_set('display_errors', strval(intval(DEV_MODE)));
ini_set('display_startup_errors', strval(intval(DEV_MODE)));
ini_set('error_reporting', '32767');
ini_set('fastcgi_read_timeout', '360');
ini_set('request_terminate_timeout', '360');

ini_set('session.auto_start', '0');
ini_set('session.use_cookies', '1');
ini_set('session.use_trans_sid', '0');
ini_set('session.use_only_cookies', '1');
ini_set('session.gc_maxlifetime', '2678400');
ini_set('session.cookie_lifetime', '2678400');

if (!session_id())
{
    session_start([
        'cookie_secure' => false,
        'cookie_httponly' => true
    ]);
}

if (extension_loaded('zlib'))
{
    ini_set("zlib.output_compression", "On");
    ini_set('zlib.output_compression_level', "7");
}

require PATH_CORE.DS.'bootstrap'.DS.'autoload.php';

# Connect database
QF('mysqli://'.DB_USER.':'.DB_PASS.'@'.DB_HOST.':'.DB_PORT.'/'.DB_BASE.'?encoding=utf8')->connect()->alias('default');
