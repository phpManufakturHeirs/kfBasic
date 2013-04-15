<?php

/**
 * kitFramework::kfBasic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\kitCommand;

use Silex\Application;

class Basic
{

    protected $app = null;
    private static $cms_parameters = null;
    protected static $cms = null;
    protected static $parameter = null;
    protected static $GET = null;
    protected static $POST = null;
    private static $message = '';

    public function __construct(Application $app, $parameters)
    {
        $this->app = $app;
        Basic::$cms_parameters = json_decode(base64_decode($parameters), true);
        Basic::$cms = Basic::$cms_parameters['cms'];
        Basic::$parameter = Basic::$cms_parameters['params'];
        Basic::$GET = Basic::$cms_parameters['GET'];
        Basic::$POST = Basic::$cms_parameters['POST'];
        $app['translator']->setLocale(Basic::$cms['locale']);
    }

    /**
     * @return the $message
     */
    public static function getMessage ()
    {
        return Basic::$message;
    }

    /**
     * @param string $message
     */
    public static function setMessage ($message)
    {
        Basic::$message .= $message;
    }

    /**
     * Check if a message is active
     *
     * @return boolean
     */
    public static function isMessage()
    {
        return !empty(Basic::$message);
    }

    /**
     * Clear the existing message(s)
     */
    public static function clearMessage()
    {
        Basic::$message = '';
    }

}