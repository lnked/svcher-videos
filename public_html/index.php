<?php

require 'init.php';

$ve = new hbattat\VerifyEmail('ed.proff@gmail.com', 'info@celebro.ru');

__($ve->get_errors());
// __($ve->get_debug());

exit(__($ve->verify()));

$app = new App;
$app->terminate();
