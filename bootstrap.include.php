<?php

/**
 * kitFramework::kfBasic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

require_once __DIR__ . '../../../../../framework/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\Loader\ArrayLoader;
use phpManufaktur\Basic\Control\UserProvider;
use phpManufaktur\Basic\Control\manufakturPasswordEncoder;
use phpManufaktur\Basic\Control\twigExtension;
use phpManufaktur\Basic\Control\Account;
use phpManufaktur\Basic\Control\forgottenPassword;
use phpManufaktur\Basic\Control\Utils;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use phpManufaktur\Basic\Control\Welcome;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use phpManufaktur\Basic\Data\Setup\Setup;
use phpManufaktur\Basic\Control\ExtensionRegister;
use phpManufaktur\Basic\Control\ExtensionCatalog;
use phpManufaktur\Updater\Updater;


// set the error handling
ini_set('display_errors', 1);
error_reporting(- 1);
ErrorHandler::register();
if ('cli' !== php_sapi_name()) {
    ExceptionHandler::register();
}


// init application
$app = new Silex\Application();

// register the Framework Utils
$app['utils'] = $app->share(function($app) {
    return new Utils($app);
});

try {
    // check for the framework configuration file
    $framework_config = $app['utils']->readConfiguration(__DIR__ . '/../../../../config/framework.json');
    // framework constants
    define('FRAMEWORK_URL', $framework_config['FRAMEWORK_URL']);
    define('FRAMEWORK_PATH', $framework_config['FRAMEWORK_PATH']);
    define('FRAMEWORK_TEMP_PATH', isset($framework_config['FRAMEWORK_TEMP_PATH']) ? $framework_config['FRAMEWORK_TEMP_PATH'] : FRAMEWORK_PATH . '/temp');
    define('FRAMEWORK_TEMP_URL', isset($framwework_config['FRAMEWORK_TEMP_URL']) ? $framework_config['FRAMEWORK_TEMP_URL'] : FRAMEWORK_URL . '/temp');
    define('FRAMEWORK_TEMPLATES', isset($framework_config['FRAMEWORK_TEMPLATES']) ? $framework_config['FRAMEWORK_TEMPLATES'] : 'default');
    define('MANUFAKTUR_PATH', FRAMEWORK_PATH . '/extension/phpmanufaktur/phpManufaktur');
    define('MANUFAKTUR_URL', FRAMEWORK_URL . '/extension/phpmanufaktur/phpManufaktur');
    define('THIRDPARTY_PATH', FRAMEWORK_PATH . '/extension/thirdparty/thirdParty');
    define('THIRDPARTY_URL', FRAMEWORK_URL . '/extension/thirdparty/thirdParty');
    define('FRAMEWORK_TEMPLATE_PATH', FRAMEWORK_PATH . '/template/framework');
    define('FRAMEWORK_TEMPLATE_URL', FRAMEWORK_URL . '/template/framework');
    define('CMS_TEMPLATE_PATH', FRAMEWORK_PATH . '/template/cms');
    define('CMS_TEMPLATE_URL', FRAMEWORK_URL . '/template/cms');
    define('CONNECT_CMS_USERS', isset($framework_config['CONNECT_CMS_USERS']) ? $framework_config['CONNECT_CMS_USERS'] : true);
    define('FRAMEWORK_SETUP', isset($framework_config['FRAMEWORK_SETUP']) ? $framework_config['FRAMEWORK_SETUP'] : true);
    define('FRAMEWORK_MEDIA_PATH', FRAMEWORK_PATH.'/media/public');
    define('FRAMEWORK_MEDIA_URL', FRAMEWORK_URL.'/media/public');
    define('FRAMEWORK_MEDIA_PROTECTED_PATH', FRAMEWORK_PATH.'/media/protected');
    define('FRAMEWORK_MEDIA_PROTECTED_URL', FRAMEWORK_URL.'/media/protected');
} catch (\Exception $e) {
    throw new \Exception('Problem setting the framework constants!', 0, $e);
}

// debug mode
$app['debug'] = (isset($framework_config['DEBUG'])) ? $framework_config['DEBUG'] : false;

// get the filesystem into the application
$app['filesystem'] = function  ()
{
    return new Filesystem();
};

$directories = array(
    FRAMEWORK_PATH . '/logfile',
    FRAMEWORK_PATH . '/temp/cache',
    FRAMEWORK_PATH . '/temp/session'
);

// check the needed temporary directories and create them if needed
if (! $app['filesystem']->exists($directories))
    $app['filesystem']->mkdir($directories);

$max_log_size = (isset($framework_config['LOGFILE_MAX_SIZE'])) ? $framework_config['LOGFILE_MAX_SIZE'] : 2 * 1024 * 1024; // 2 MB
$log_file = FRAMEWORK_PATH . '/logfile/kit2.log';
if ($app['filesystem']->exists($log_file) && (filesize($log_file) > $max_log_size)) {
    $app['filesystem']->remove(FRAMEWORK_PATH . '/logfile/kit2.log.bak');
    $app['filesystem']->rename($log_file, FRAMEWORK_PATH . '/logfile/kit2.log.bak');
}

date_default_timezone_set('Europe/Berlin');
// register monolog
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => $log_file
));
$app['monolog']->addInfo('MonologServiceProvider registered.');

try {
    // read the CMS configuration
    $cms_config = $app['utils']->readConfiguration(FRAMEWORK_PATH . '/config/cms.json');
    // setting the CMS constants
    define('CMS_PATH', $cms_config['CMS_PATH']);
    define('CMS_URL', $cms_config['CMS_URL']);
    define('CMS_MEDIA_PATH', $cms_config['CMS_MEDIA_PATH']);
    define('CMS_MEDIA_URL', $cms_config['CMS_MEDIA_URL']);
    define('CMS_TEMP_PATH', $cms_config['CMS_TEMP_PATH']);
    define('CMS_TEMP_URL', $cms_config['CMS_TEMP_URL']);
    define('CMS_ADMIN_PATH', $cms_config['CMS_ADMIN_PATH']);
    define('CMS_ADMIN_URL', $cms_config['CMS_ADMIN_URL']);
    define('CMS_TYPE', $cms_config['CMS_TYPE']);
    define('CMS_VERSION', $cms_config['CMS_VERSION']);
} catch (\Exception $e) {
    throw new \Exception('Problem setting the CMS constants!', 0, $e);
}
$app['monolog']->addInfo('CMS constants defined.');

try {
    // read the doctrine configuration
    $doctrine_config = $app['utils']->readConfiguration(FRAMEWORK_PATH . '/config/doctrine.cms.json');
    define('CMS_TABLE_PREFIX', $doctrine_config['TABLE_PREFIX']);
    define('FRAMEWORK_TABLE_PREFIX', $doctrine_config['TABLE_PREFIX'] . 'kit2_');
    $app->register(new Silex\Provider\DoctrineServiceProvider(), array(
        'db.options' => array(
            'driver' => 'pdo_mysql',
            'dbname' => $doctrine_config['DB_NAME'],
            'user' => $doctrine_config['DB_USERNAME'],
            'password' => $doctrine_config['DB_PASSWORD'],
            'host' => $doctrine_config['DB_HOST'],
            'port' => $doctrine_config['DB_PORT']
        )
    ));
} catch (\Exception $e) {
    throw new \Exception('Problem initilizing Doctrine!', 0, $e);
}
$app['monolog']->addInfo('DoctrineServiceProvider registered');

// register the session handler
$app->register(new Silex\Provider\SessionServiceProvider(), array(
    'session.storage.save_path' => FRAMEWORK_PATH . '/temp/session',
    'session.storage.options' => array(
        'cookie_lifetime' => 0
    )
));
$app['monolog']->addInfo('SessionServiceProvider registered.');

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app['monolog']->addInfo('UrlGeneratorServiceProvider registered.');

// default language
$locale = 'en';
// quick and dirty ... try to detect the favorised language - to be improved!
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $langs = array();
    // break up string into pieces (languages and q factors)
    preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);
    if (count($lang_parse[1]) > 0) {
        foreach ($lang_parse[1] as $lang) {
            if (false === (strpos($lang, '-'))) {
                // only the country sign like 'de'
                $locale = strtolower($lang);
            } else {
                // perhaps something like 'de-DE'
                $locale = strtolower(substr($lang, 0, strpos($lang, '-')));
            }
            break;
        }
    }
}

// register the Translator
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.messages' => array(),
    'locale' => $locale,
    'locale_fallback' => 'en'
));

$app['translator'] = $app->share($app->extend('translator', function  ($translator, $app)
{
    $translator->addLoader('array', new ArrayLoader());
    return $translator;
}));
$app['monolog']->addInfo('Translator Service registered. Added ArrayLoader to the Translator');

// load the /Basic language files
$app['utils']->addLanguageFiles(MANUFAKTUR_PATH.'/Basic/Data/Locale');

// register Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => $app['debug'] ? false : FRAMEWORK_PATH . '/temp/cache/',
        'strict_variables' => $app['debug'] ? true : false,
        'debug' => $app['debug'] ? true : false,
        'autoescape' => false
    )
));

// set namespaces for phpManufaktur, thirdParty, framework and CMS template
$app['twig.loader.filesystem']->addPath(MANUFAKTUR_PATH, 'phpManufaktur');
$app['twig.loader.filesystem']->addPath(THIRDPARTY_PATH, 'thirdParty');
$app['twig.loader.filesystem']->addPath(FRAMEWORK_TEMPLATE_PATH, 'framework');
$app['twig.loader.filesystem']->addPath(CMS_TEMPLATE_PATH, 'CMS');
// IMPORTANT: define these namespaces also in phpManufaktur\Basic\Control\Utils\templateFile()

$app['twig'] = $app->share($app->extend('twig', function  ($twig, $app)
{
    // add global variables, functions etc. for the templates
    $twig->addExtension(new twigExtension($app));
    if ($app['debug']) {
        $twig->addExtension(new Twig_Extension_Debug());
    }
    return $twig;
}));

$app['monolog']->addInfo('TwigServiceProvider registered.');

// register Validator Service
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app['monolog']->addInfo('Validator Service Provider registered.');

// register the FormServiceProvider
$app->register(new Silex\Provider\FormServiceProvider());
$app['monolog']->addInfo('Form Service registered.');

// register the HTTP Cache Service
$app->register(new Silex\Provider\HttpCacheServiceProvider(), array(
    'http_cache.cache_dir' => FRAMEWORK_PATH . '/temp/cache/'
));
$app['monolog']->addInfo('HTTP Cache Service registered.');

// register the SwiftMailer
try {
    $swift_config = $app['utils']->readConfiguration(FRAMEWORK_PATH . '/config/swift.cms.json');
    $app->register(new Silex\Provider\SwiftmailerServiceProvider());
    $app['swiftmailer.options'] = array(
        'host' => isset($swift_config['SMTP_HOST']) ? $swift_config['SMTP_HOST'] : 'localhost',
        'port' => isset($swift_config['SMTP_PORT']) ? $swift_config['SMTP_PORT'] : '25',
        'username' => $swift_config['SMTP_USERNAME'],
        'password' => $swift_config['SMTP_PASSWORD'],
        'encryption' => isset($swift_config['SMTP_ENCRYPTION']) ? $swift_config['SMTP_ENCRYPTION'] : null,
        'auth_mode' => isset($swift_config['SMTP_AUTH_MODE']) ? $swift_config['SMTP_AUTH_MODE'] : null
    );
    define('SERVER_EMAIL_ADDRESS', $swift_config['SERVER_EMAIL']);
    define('SERVER_EMAIL_NAME', $swift_config['SERVER_NAME']);
    $app['monolog']->addInfo('SwiftMailer Service registered');
} catch (\Exception $e) {
    throw new \Exception('Problem initilizing the SwiftMailer!');
}


if (FRAMEWORK_SETUP) {
    // execute the setup routine for kitFramework::Basic
    $Setup = new Setup($app);
    $Setup->exec();
}

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'admin' => array(
            'pattern' => '^/admin',
            'form' => array(
                'login_path' => '/login',
                'check_path' => '/admin/login_check'
            ),
            'users' => $app->share(function  () use( $app)
            {
                return new UserProvider($app);
            }),
            'logout' => array(
                'logout_path' => '/admin/logout'
            )
        )
    ),
    'security.encoder.digest' => $app->share(function  ($app)
    {
        return new manufakturPasswordEncoder($app);
    })
));


$app->get('/login', function (Request $request) use($app)
{
    return $app['twig']->render($app['utils']->templateFile('@phpManufaktur/Basic/Template', 'login.twig'), array(
        'error' => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
});

$app->get('/password/forgotten', function () use($app)
{
    // user has forgot the password
    $forgotPassword = new forgottenPassword($app);
    return $forgotPassword->dialogForgottenPassword();
});

$app->match('/password/reset', function (Request $request) use ($app)
{
    // send the user a GUID to reset the password
    $resetPassword = new forgottenPassword($app);
    return $resetPassword->dialogResetPassword();
});

$app->match('/password/retype', function (Request $request) use ($app) {
    $retypePassword = new forgottenPassword($app);
    return $retypePassword->dialogRetypePassword();
});

$app->get('/password/create/{guid}', function ($guid) use ($app)
{
    // validate the GUID and create a new password
    $createPassword = new forgottenPassword($app);
    return $createPassword->dialogCreatePassword($guid);
});

$app->get('/admin/account', function  (Request $request) use($app)
{
    // user the user account dialog
    $account = new Account($app);
    return $account->showDialog();
});

$scan_paths = array(
    MANUFAKTUR_PATH,
    THIRDPARTY_PATH
);

// loop through /phpManufaktur and /thirdParty to include bootstrap extensions
foreach ($scan_paths as $scan_path) {
    $entries = scandir($scan_path);
    foreach ($entries as $entry) {   	
        if (is_dir($scan_path . '/' . $entry)) {
        	  if (file_exists($scan_path . '/' . $entry . '/bootstrap.include.php')) {
                // don't load the Basic bootstrap again               
                if ($entry == 'Basic') continue;
                // include the bootstrap extension
                include_once $scan_path . '/' . $entry . '/bootstrap.include.php';
            }
        }
    }
}

// catch all kitCommands
$app->match('/kit_command/{command}/{params}', function (Request $request, $command, $params) use ($app) {
    try {
        $subRequest = Request::create('/command/'.$command.'/'.$params, 'GET');
        // important: we dont want that app->handle() catch errors, so set the third parameter to false!
        $result = $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
    } catch (\Exception $e) {
        $parameters = json_decode(base64_decode($params), true);
        if (isset($parameters['params']['debug']) && ((strtolower($parameters['params']['debug']) == 'true') || 
            ($parameters['params']['debug'] == 1) || ($parameters['params']['debug'] == ''))) {
            // the debug parameter isset, so return the error information
            $file = substr($e->getFile(), strlen(FRAMEWORK_PATH));
            $result = <<<EOD
    <div style="border:1px solid #ccc;color:#000;background-color:#fff8dc;margin:20px 0 10px 0;padding:10px;font-size:13px;">
      <p>Error executing the kitCommand <b>$command</b>:</p>
      <p><b>File:</b> $file</p>
      <p><b>Line:</b> {$e->getLine()}</p>
      <p><b>Message:</b> {$e->getMessage()}</p>
    </div>
EOD;
        }
        else {
            // no debug parameter, we assume that the kitCommand does not exists
            $result = "~~ <b>Error</b>: Can't execute the kitCommand: <i>$command</i> ~~";
        }
    }
    return $result;
});

$app->get('/', function(Request $request) use ($app) {
    $subRequest = Request::create('/welcome', 'GET');
    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
});

$app->get('/admin', function(Request $request) use ($app) {
    $subRequest = Request::create('/welcome', 'GET');
    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
});

$app->get('/admin/welcome', function (Request $request) use ($app) {
    $Welcome = new Welcome($app);
    return $Welcome->exec();
});

$app->match('/welcome', function (Request $request) use ($app) {
    $Welcome = new Welcome($app);
    return $Welcome->exec();
});

$app->match('/welcome/cms/{cms}', function ($cms) use ($app) {
    // get the CMS info parameters
    $cms = json_decode(base64_decode($cms), true);

    // save them partial into session
    $app['session']->set('CMS_TYPE', $cms['type']);
    $app['session']->set('CMS_VERSION', $cms['version']);
    $app['session']->set('CMS_LOCALE', $cms['locale']);
    $app['session']->set('CMS_USER_NAME', $cms['username']);

    // auto login into the admin area and then exec the welcome dialog
    $secureAreaName = 'admin';
    // @todo the access control is very soft and the ROLE is actually not checked!
    $user = new User($cms['username'],'', array('ROLE_ADMIN'), true, true, true, true);
    $token = new UsernamePasswordToken($user, null, $secureAreaName, $user->getRoles());
    $app['security']->setToken($token);
    $app['session']->set('_security_'.$secureAreaName, serialize($token) );

    $usage = ($cms['target'] == 'cms') ? $cms['type'] : 'framework';

    // sub request to the welcome dialog
    $subRequest = Request::create('/admin/welcome', 'GET', array('usage' => $usage));
    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
});

$app->get('/admin/scan/extensions', function () use ($app) {
    $register = new ExtensionRegister($app);
    $register->scanDirectories(ExtensionRegister::GROUP_PHPMANUFAKTUR);
    $register->scanDirectories(ExtensionRegister::GROUP_THIRDPARTY);
    $Welcome = new Welcome($app);
    $Welcome->setMessage($app['translator']->trans('<p>Successfull scanned the kitFramework for installed extensions.</p>'));
    return $Welcome->exec();
});

$app->get('/admin/scan/catalog', function() use($app) {
    $catalog = new ExtensionCatalog($app);
    $catalog->getOnlineCatalog();
    $Welcome = new Welcome($app);
    $Welcome->setMessage($app['translator']->trans('<p>Successfull scanned the kitFramework online catalog for available extensions.</p>'));
    return $Welcome->exec();
});

$app->get('/admin/updater/get/github/{organization}/{repository}/{usage}', function (Request $request, $organization, $repository, $usage) use ($app) {
    $message = '';
    if (file_exists(MANUFAKTUR_PATH.'/Updater/Updater.php')) {
        unlink(MANUFAKTUR_PATH.'/Updater/Updater.php');
        rmdir(MANUFAKTUR_PATH.'/Updater');
    }
    if (!file_exists(MANUFAKTUR_PATH.'/Updater/Updater.php')) {
        mkdir(MANUFAKTUR_PATH.'/Updater');
        copy(MANUFAKTUR_PATH.'/Basic/Control/Updater/Updater.php', MANUFAKTUR_PATH.'/Updater/Updater.php');
    }
    $updater = new Updater($app);
    return $updater->getLastGithubRepository($organization, $repository);
});

$app->match('/command/test/{params}', function(Request $request, $params) use ($app) {
    /*
    $param = json_decode(base64_decode($params), true);
    ob_start();
    echo "<pre>";
    print_r($param);
    echo "</pre>";
    $param = ob_get_clean();
    return $param;
    */
    // get all routing objects
    $patterns = $app['routes']->getIterator()->current()->all();
    // walk through the routing objects
    foreach ($patterns as $pattern) {
        $match = $pattern->getPattern();
        echo "$match<br />";
        if (null !== ($opt = $pattern->getOption('info')))
            echo "Info: $opt<br>";
    }
    return 'ok';
})
->setOption('info', MANUFAKTUR_PATH.'/winCalc/command.wincalc.json');

if (FRAMEWORK_SETUP) {
    // the setup flag was set to TRUE, now we assume that we can set it to FALSE
    $framework_config['FRAMEWORK_SETUP'] = false;
    if (! file_put_contents(FRAMEWORK_PATH. '/config/framework.json', json_encode($framework_config)))
        throw new \Exception('Can\'t write the configuration file for the framework!');
    $app['monolog']->addInfo('Finished kitFramework setup.');
}

if ($app['debug'])
    $app->run();
else
    $app['http_cache']->run();
