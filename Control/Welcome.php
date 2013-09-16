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
use phpManufaktur\Basic\Data\Security\Users as kitFrameworkUser;
use phpManufaktur\Basic\Data\CMS\Users as cmsUser;

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

    /**
     * Get the form for the first login dialog
     *
     * @param Application $app
     * @param array $data
     */
    protected function getLoginForm(Application $app, $data=array())
    {
        return $app['form.factory']->createBuilder('form')
            ->add('name', 'text', array(
                'label' => 'Username',
                'data' => isset($data['user_name']) ? $data['user_name'] : '',
                'disabled' => true
            ))
            ->add('username', 'hidden', array(
                'data' => isset($data['user_name']) ? $data['user_name'] : ''
            ))
            ->add('password', 'password')
            ->add('email', 'hidden', array(
                'data' => isset($data['user_email']) ? $data['user_email'] : '',
            ))
            ->add('display_name', 'hidden', array(
                'data' => isset($data['display_name']) ? $data['display_name'] : ''
            ))
            ->add('cms_type', 'hidden', array(
                'data' => isset($data['cms_array']['type']) ? $data['cms_array']['type'] : 'framework'
            ))
            ->getForm();
    }

    /**
     * Return the first login dialog
     *
     * @param Application $app
     * @param array $data
     */
    protected function firstLogin(Application $app, $data)
    {
        $form = $this->getLoginForm($app, $data);

        return $app['twig']->render($app['utils']->templateFile('@phpManufaktur/Basic/Template', 'framework/first.login.twig'),
            array(
                'usage' => self::$usage,
                'form' => $form->createView(),
                'cms_type' => $data['cms_array']['type'],
                'display_name' => $data['display_name'],
                'message' => $this->getMessage()
            ));
    }

    /**
     * Check the first login, create a kitFramework user, auto-login the user
     * and execute the welcome dialog
     *
     * @param Application $app
     * @throws \Exception
     */
    public function checkFirstLogin(Application $app)
    {
        self::$usage = $app['request']->get('usage', 'framework');

        $form = $this->getLoginForm($app);
        $form->bind($app['request']);
        if ($form->isValid()) {
            $user = $form->getData();
            // check if the password is identical with the CMS account
            $cmsUser = new cmsUser($app);
            if (false === ($cmsUserData = $cmsUser->selectUser($user['username']))) {
                // terrible wrong - user does not exists
                throw new \Exception("The user {$user['username']} does not exists.");
            }
            if (md5($user['password']) != $cmsUserData['password']) {
                // the password is not identical
                $this->setMessage($app['translator']->trans('The password you typed in is not correct, please try again.'));
                return $app['twig']->render($app['utils']->templateFile('@phpManufaktur/Basic/Template', 'framework/first.login.twig'),
                    array(
                        'usage' => self::$usage,
                        'form' => $form->createView(),
                        'cms_type' => $user['cms_type'],
                        'display_name' => $user['display_name'],
                        'message' => $this->getMessage()
                    ));
            }

            $kitFrameworkUser = new kitFrameworkUser($app);
            // create the kitFramework User
            $data = array(
                'username' => $user['username'],
                'email' => $user['email'],
                'password' => $kitFrameworkUser->encodePassword($user['password']),
                'displayname' => $user['display_name'],
                'roles' => 'ROLE_ADMIN'
            );
            $kitFrameworkUser->insertUser($data);

            // auto login into the admin area and then exec the welcome dialog
            $secureAreaName = 'admin';
            // @todo the access control is very soft and the ROLE is actually not checked!
            $user = new User($user['username'], $user['password'], array('ROLE_ADMIN'), true, true, true, true);
            $token = new UsernamePasswordToken($user, null, $secureAreaName, $user->getRoles());
            $app['security']->setToken($token);
            $app['session']->set('_security_'.$secureAreaName, serialize($token) );

            // sub request to the welcome dialog
            $subRequest = Request::create('/admin/welcome', 'GET', array('usage' => self::$usage));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }
        else {
            throw new \Exception("Ooops - the form is not valid, please try it again!");
        }
    }

    /**
     * Prepare the execution of the welcome dialog
     *
     * @param Application $app
     * @param string $cms
     */
    public function welcomeCMS(Application $app, $cms)
    {
        // get the CMS info parameters
        $cms = json_decode(base64_decode($cms), true);

        self::$usage = ($cms['target'] == 'cms') ? $cms['type'] : 'framework';

        $cmsUser = new cmsUser($app);
        $is_admin = false;
        if ((false === ($cmsUserData = $cmsUser->selectUser($cms['username'], $is_admin))) || !$is_admin) {
            // the user is no CMS Administrator, deny access!
            return $app['twig']->render($app['utils']->templateFile('@phpManufaktur/Basic/Template', 'framework/admins.only.twig'),
                array('usage' => self::$usage));
        }

        $kitFrameworkUser = new kitFrameworkUser($app);
        if (!$kitFrameworkUser->existsUser($cms['username'])) {
            // this user does not exists in the kitFramework User database
            $data = array(
                'user_name' => $cmsUserData['username'],
                'user_email' => $cmsUserData['email'],
                'display_name' => $cmsUserData['display_name'],
                'cms_array' => $cms
            );
            return $this->firstLogin($app, $data);
        }

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


        // sub request to the welcome dialog
        $subRequest = Request::create('/admin/welcome', 'GET', array('usage' => self::$usage));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

} // class Account
