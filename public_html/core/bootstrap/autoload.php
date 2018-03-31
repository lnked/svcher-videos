<?php declare(strict_types = 1);

require_once PATH_VENDORS.DS.'autoload.php';

# Functions
#
$fn_list = [
    'fn.inc.php',
    'fn.predicts.php'
];

foreach ($fn_list as $file)
{
    if (file_exists(PATH_CORE.DS.'functions'.DS.$file))
    {
        require PATH_CORE.DS.'functions'.DS.$file;
    }
}

# Autoload
#
spl_autoload_register(function ($_class) {
    clearstatcache(true);

    $_temp = explode('\\', strtolower($_class));
    $_class = end($_temp);

    if (file_exists(PATH_CORE.DS.'classes'.DS.$_class.'.class.php')) {
        require_once PATH_CORE.DS.'classes'.DS.$_class.'.class.php';
    }
});
