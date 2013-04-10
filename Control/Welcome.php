<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control;

use Silex\Application;
use phpManufaktur\Basic\Control\ExtensionRegister;

class Welcome
{

    protected $app = null;
    protected static $message;
    protected static $usage = 'framework';

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct (Application $app)
    {
        $this->app = $app;
        $cms = $this->app['request']->get('usage');
        self::$usage = is_null($cms) ? 'framework' : $cms;
    } // __construct()

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
    public function exec ()
    {
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

        return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'welcome.twig'), array(
            'usage' => self::$usage,
            'iframe_add_height' => '300',
            'catalog_items' => $catalog_items,
            'register_items' => $register_items,
            'message' => $this->getMessage(),
            'scan_extensions' => FRAMEWORK_URL.'/admin/scan/extensions?usage='.self::$usage,
            'scan_catalog' => FRAMEWORK_URL.'/admin/scan/catalog?usage='.self::$usage
        ));
    }

} // class Account