<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)) . DS);
define('APP', ROOT . 'app' . DS);
define('SYSTEM', ROOT . 'system' . DS);
define('_ASSETS', ROOT . 'assets' . DS);
define('TPL', ROOT . 'templates' . DS);
define('TIME', time());

if (version_compare(PHP_VERSION, '7.2', '<') || version_compare(PHP_VERSION, '7.4.33', '>')) {
    // If the version below 7.2, we stops the script and displays an error
    die('<div style="text-align: center; font-size: xx-large">'
        . '<h3 style="color: #dd0000">ERROR: outdated version of PHP</h3>'
        . 'Your needs PHP 7.4'
        . '</div>');
}

// Global config
require(ROOT . 'configs' . DS . 'init.php');

// Autoload
require(SYSTEM . 'autoload.php');
require(ROOT . 'vendor' . DS . 'autoload.php');

// global function
require(SYSTEM . 'functions.php');

if (extension_loaded('zlib')) {
    ini_set('zlib.output_compression', 'On');
    ini_set('zlib.output_compression_level', 3);
}

// Register Services
$config = Container::get(Config::class);
$services = $config->get('services');

foreach ($services as $service) {
    Container::get($service)->register();
}
