<?php

/**
 * TemplateTools
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/TemplateTools
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if (!defined('BOOTSTRAP_PATH')) {
    trigger_error('Missing the BOOTSTRAP_PATH!');
}

require_once realpath(BOOTSTRAP_PATH.'/framework/autoload.php');

// set the error handling
ini_set('display_errors', 1);
error_reporting(-1);
Symfony\Component\HttpKernel\Debug\ErrorHandler::register();
if ('cli' !== php_sapi_name()) {
    Symfony\Component\HttpKernel\Debug\ExceptionHandler::register();
}

$migrate = new Silex\Application();

$migrate['debug'] = true;

$migrate->get('/', function () use ($migrate) {
    $migrate->abort(500, 'REady');
    return 'xxx';
});

$migrate->run();
