<?php

namespace PHPDocSearch;

use \PHPDocSearch\Web\Request,
    \PHPDocSearch\Web\Router,
    \PHPDocSearch\Web\ControllerFactory,
    \PHPDocSearch\Web\ViewFactory;

ini_set('display_errors', 0);
require __DIR__ . '/autoload.php';

set_exception_handler(function(\Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
    echo 'An unrecoverable error has occurred. Please contact the system administrator.';
    trigger_error('An unrecoverable error occurred: ' . $e->getMessage(), E_USER_ERROR);
    exit;
});

$baseDir = realpath(__DIR__ . '/../../');
$config = new Config($baseDir);
$request = new Request($baseDir, $config, $_GET, $_COOKIE, $_SERVER);

$router = new Router(new ControllerFactory);
$controller = $router->route($request);

$view = $controller->handleRequest();
echo $view->render();

/*
if (strtoupper($env->getServerParam('REQUEST_METHOD')) !== 'GET') {
    header($env->getServerParam('SERVER_PROTOCOL') . ' 405 Method Not Allowed');
    exit($env->getServerParam('REQUEST_METHOD') . ' method requests are not accepted by this server');
}
*/
