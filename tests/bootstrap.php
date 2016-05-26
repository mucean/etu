<?php

if (!is_file(__DIR__ . DIRECTORY_SEPARATOR . '../vendor/autoload.php')) {
    die('Run `composer install` first before Run `phpunit`' . PHP_EOL);
}

defined('TEST') || define('TEST', true);

require_once(__DIR__ . DIRECTORY_SEPARATOR . '../vendor/autoload.php');

\Etu\Application::registerNamespace(__DIR__, '\\Tests');
