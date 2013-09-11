<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control;

use Silex\Application;
use phpManufaktur\Basic\Control\ExtensionRegister;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Display a welcome to the kitFramework dialog
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class Welcome
{

    protected $app = null;
    protected static $message;
    protected static $usage = 'framework';

    /**
     * @return the $message
     */
    public static function getMessage ()
    {
        return self::$message;
    }

    /**
     * @param string $message
     */
    public static function setMessage ($message)
    {
        self::$message .= $message;
    }

    /**
     * Check if a message is active
     *
     * @return boolean
     */
    public static function isMessage()
    {
        return !empty(self::$message);
    }

    /**
     * Clear the existing message(s)
     */
    public static function clearMessage()
    {
        self::$message = '';
    }

    public static function setUsage($usage)
    {
        self::$usage = $usage;
    }

    public static function getUsage()
    {
        return self::$usage;
    }

    /**
     * Execute the welcome dialog
     */
    public function exec(Application $app)
    {
        $this->app = $app;
        $cms = $this->app['request']->get('usage');
        self::$usage = is_null($cms) ? 'framework' : $cms;


        if (!$this->app['security']->isGranted('ROLE_ADMIN')) {
            return 'anonymous...';
        }
        /*
        $token = $app['security']->getToken();
        if (is_null($token))
            return 'ANONYMOUS';
        // get user by token

        $user = $token->getUser();
        echo '<pre>';
        var_dump($user);
        echo '</pre>';
*/
        // use reflection to dynamical load a class
        $reflection = new \ReflectionClass('phpManufaktur\\Basic\\Control\\ExtensionCatalog');
        $catalog = $reflection->newInstanceArgs(array($this->app));

        try {
            $catalog->getOnlineCatalog();
            $this->setMessage($catalog->getMessage());
        } catch (\Exception $e) {
            $this->setMessage($e->getMessage());
        }

        $catalog = new ExtensionCatalog($this->app);
        $catalog_items = $catalog->getAvailableExtensions();

        $register = new ExtensionRegister($this->app);
        $register_items = $register->getInstalledExtensions();

        return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'framework/welcome.twig'), array(
            'usage' => self::$usage,
            'iframe_add_height' => '300',
            'catalog_items' => $catalog_items,
            'register_items' => $register_items,
            'message' => $this->getMessage(),
            'scan_extensions' => FRAMEWORK_URL.'/admin/scan/extensions?usage='.self::$usage,
            'scan_catalog' => FRAMEWORK_URL.'/admin/scan/catalog?usage='.self::$usage
        ));
    }

    public function welcomeCMS(Application $app, $cms)
    {
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
    }

} // class Account
