<?php declare(strict_types = 1);

date_default_timezone_set('Europe/Moscow');

define('DS', DIRECTORY_SEPARATOR);
define('PATH_ROOT', __DIR__);
define('PATH_CORE', PATH_ROOT.DS.'core');
define('PATH_VENDORS', PATH_CORE.DS.'vendors');
define('PATH_TEMPLATE', PATH_CORE.DS.'template');

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
// QF('mysqli://'.DB_USER.':'.DB_PASS.'@'.DB_HOST.':'.DB_PORT.'/'.DB_BASE.'?encoding=utf8')->connect()->alias('default')->tablePrefix(DB_PREF);
