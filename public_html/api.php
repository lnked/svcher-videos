<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

// use Tygh\Registry;

// Area will be defined in Api::defineArea.
define('API', true);
define('NO_SESSION', true);

try {
    require 'init.php';

    /**
     * @var Api
     */
    $api = new Api;

    $api->handleRequest();

} catch (Tygh\Exceptions\AException $e) {
    $e->output();
}
