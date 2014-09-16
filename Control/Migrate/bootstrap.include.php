<?php

/**
 * kitFramework::Migrate
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

if (!defined('BOOTSTRAP_PATH')) {
    trigger_error('Missing the BOOTSTRAP_PATH!');
}

require_once realpath(BOOTSTRAP_PATH.'/framework/autoload.php');

use phpManufaktur\Basic\Control\Utils;
use Symfony\Component\Filesystem\Filesystem;
use phpManufaktur\Basic\Control\Account\UserProvider;
use phpManufaktur\Basic\Control\Account\manufakturPasswordEncoder;

// set the error handling
ini_set('display_errors', 1);
error_reporting(-1);
Symfony\Component\HttpKernel\Debug\ErrorHandler::register();
if ('cli' !== php_sapi_name()) {
    Symfony\Component\HttpKernel\Debug\ExceptionHandler::register();
}

$migrate = new Silex\Application();

// register the Framework Utils
$migrate['utils'] = $migrate->share(function($migrate) {
    return new Utils($migrate);
});

// get the filesystem into the application
$migrate['filesystem'] = function() {
    return new Filesystem();
};

// init the firewall
$migrate->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'general' => array(
            'pattern' => '^/',
            'anonymous' => false,
            'form' => array(
                'login_path' => '/login',
                'check_path' => '/admin/login_check'
            ),
            'users' => $migrate->share(function() use($migrate)
            {
                return new UserProvider($migrate);
            }),
            'logout' => array(
                'logout_path' => '/admin/logout',
                'target_url' => '/goodbye'
            ),
            'switch_user' => array(
                'parameter' => '_switch_user',
                'role' => 'ROLE_ALLOWED_TO_SWITCH'
            )
        )
    ),
    'security.encoder.digest' => $migrate->share(function ($migrate)
    {
        return new manufakturPasswordEncoder($migrate);
    }),
    'security.access_rules' => array(
        array('^/', 'ROLE_ADMIN')
    )
));

$migrate['debug'] = true;

$migrate->get('/', function () use ($migrate) {
    // check for the framework configuration file
    $framework_config = $migrate['utils']->readConfiguration(realpath(BOOTSTRAP_PATH . '/config/framework.json'));
    print_r($framework_config);
    //$migrate->abort(500, 'REady');
    return 'xxx';
});

$migrate->get('/login',
    // the general login dialog
    'phpManufaktur\Basic\Control\Account\Login::exec');

$migrate->run();
