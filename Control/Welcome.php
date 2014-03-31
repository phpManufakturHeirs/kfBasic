<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control;

use Silex\Application;
use phpManufaktur\Basic\Control\ExtensionRegister;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use phpManufaktur\Basic\Data\CMS\SearchSection;
use phpManufaktur\Basic\Control\CMS\InstallSearch;
use phpManufaktur\Basic\Control\Pattern\Alert;

/**
 * Display a welcome to the kitFramework dialog
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class Welcome extends Alert
{

    protected static $usage = 'framework';

    /**
     * Initialize the Welcome dialog
     *
     * @param Application $app
     */
    protected function initWelcome(Application $app)
    {
        $this->initialize($app);

        // grant that the updater is installed in the separated directory and is actual
        if (!file_exists(MANUFAKTUR_PATH.'/Updater')) {
            $app['filesystem']->mkdir(MANUFAKTUR_PATH.'/Updater');
        }
        $app['filesystem']->copy(MANUFAKTUR_PATH.'/Basic/Control/Updater/Updater.php', MANUFAKTUR_PATH.'/Updater/Updater.php', true);

        self::$usage = $this->app['request']->get('usage', 'framework');

        // check if the search addon is installed
        if (!file_exists(CMS_PATH.'/modules/kit_framework_search')) {
            $InstallSearch = new InstallSearch($app);
            $InstallSearch->exec();
        }

        // check if the search section in the CMS exists
        if (file_exists(CMS_PATH.'/modules/kit_framework_search')) {
            $SearchSection = new SearchSection();
            $SearchSection->addSearchSection($app);
        }

        // set the locale from the CMS locale
        $app['translator']->setLocale($app['session']->get('CMS_LOCALE', 'en'));
    }

    /**
     * Execute the welcome dialog. This is the main procedure, this dialog will
     * be also executed from inside the CMS after automatic authentication with
     * the controllerCMS()
     *
     */
    public function controllerFramework(Application $app)
    {
        $this->initWelcome($app);

        if (null !== ($install = $app['session']->get('FINISH_INSTALLATION', null))) {
            // get the messages from the installation
            $this->setAlertUnformatted($install['message']);
            foreach ($install['execute_route'] as $route) {
                // execute the install & update routes
                $subRequest = Request::create($route, 'GET', array('usage' => self::$usage));
                $response = $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
                $this->setAlert($response->getContent(), array(), self::ALERT_TYPE_INFO);
            }
            // remove the session
            $app['session']->remove('FINISH_INSTALLATION');
            // now scan for the installed extensions
            $register = new ExtensionRegister($app);
            $register->scanDirectories(ExtensionRegister::GROUP_PHPMANUFAKTUR);
            $register->scanDirectories(ExtensionRegister::GROUP_THIRDPARTY);
        }

        $catalog = new ExtensionCatalog($app);

        try {
            $catalog->getOnlineCatalog();
        } catch (\Exception $e) {
            $this->setAlert($e->getMessage(), array(), self::ALERT_TYPE_WARNING);
        }

        $accepted_items = explode(',', CATALOG_ACCEPT_EXTENSION);
        $cat_items = $catalog->getAvailableExtensions($app['translator']->getLocale());
        $catalog_items = array();
        foreach ($cat_items as $item) {
            // show only catalog items which have the accepted release status
            if (isset($item['release_status']) && in_array($item['release_status'], $accepted_items)) {
                $catalog_items[] = $item;
            }
        }

        $register = new ExtensionRegister($this->app);
        $register_items = $register->getInstalledExtensions();

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template',
            'framework/welcome.twig'),
            array(
                'usage' => self::$usage,
                'catalog_items' => $catalog_items,
                'register_items' => $register_items,
                'alert' => $this->getAlert(),
                'scan_extensions' => FRAMEWORK_URL.'/admin/scan/extensions?usage='.self::$usage,
                'scan_catalog' => FRAMEWORK_URL.'/admin/scan/catalog?usage='.self::$usage
            ));
    }

    /**
     * Prepare the execution of the welcome dialog
     *
     * @param Application $app
     * @param string $cms
     */
    public function controllerCMS(Application $app, $cms)
    {
        // get the CMS info parameters
        $cms_string = $cms;
        $cms = json_decode(base64_decode($cms), true);

        $app['request']->request->set('usage', ($cms['target'] == 'cms') ? $cms['type'] : 'framework');
        $this->initWelcome($app);

        if (!$app['account']->checkUserIsCMSAdministrator($cms['username'])) {
            // the user is no CMS Administrator, deny access!
            $this->setAlert('Sorry, but only Administrators are allowed to access this kitFramework extension.',
                array(), self::ALERT_TYPE_WARNING);
            return $this->app['twig']->render($this->app['utils']->getTemplateFile(
                '@phpManufaktur/Basic/Template', 'framework/alert.twig'),
                array(
                    'usage' => self::$usage,
                    'title' => 'Access denied',
                    'alert' => $this->getAlert()
                ));
        }

        if (!$app['account']->checkUserHasFrameworkAccount($cms['username'])) {
            // this user does not exists in the kitFramework User database
            $subRequest = Request::create('/login/first/cms', 'POST', array(
                'usage' => self::$usage,
                'username' => $cms['username'],
                'roles' => array('ROLE_ADMIN'),
                'auto_login' => true,
                'secured_area' => 'general',
                'redirect' => "/welcome/cms/$cms_string"
            ));
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        // save them partial into session
        $app['session']->set('CMS_TYPE', $cms['type']);
        $app['session']->set('CMS_VERSION', $cms['version']);
        $app['session']->set('CMS_LOCALE', $cms['locale']);
        $app['session']->set('CMS_USER_NAME', $cms['username']);

        // auto login the CMS user into the secured area with admin privileges
        $app['account']->loginUserToSecureArea($cms['username'], array('ROLE_ADMIN'));

        // sub request to the welcome dialog
        $subRequest = Request::create('/admin/welcome', 'GET', array('usage' => self::$usage));
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

}
