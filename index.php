<?php

define('_MVC_START', microtime(true));

require('system/bootstrap.php');

/** @var Kernel */
$kernel = Container::get(Kernel::class);

$kernel->run(Container::get(Request::class));
