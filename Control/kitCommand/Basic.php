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
    protected static $parameter_string = null;
    protected static $cms = null;
    protected static $parameter = null;
    protected static $GET = null;
    protected static $POST = null;
    protected static $SESSION = null;
    private static $message = '';
    protected static $preferred_template = null;
    protected static $frame = null;

    public function __construct(Application $app, $parameters)
    {
        $this->app = $app;
        Basic::$parameter_string = $parameters;
        Basic::$cms_parameters = json_decode(base64_decode($parameters), true);
        Basic::$cms = Basic::$cms_parameters['cms'];
        Basic::$parameter = Basic::$cms_parameters['params'];
        Basic::$GET = Basic::$cms_parameters['GET'];
        Basic::$POST = Basic::$cms_parameters['POST'];
        $app['translator']->setLocale(Basic::$cms['locale']);
        // check for the preferred template
        Basic::$preferred_template = (isset(Basic::$parameter['template'])) ? Basic::$parameter['template'] : '';
        Basic::$frame = array(
            'id' => (isset(Basic::$parameter['frame_id'])) ? Basic::$parameter['frame_id'] : 'kitframework_iframe',
            'name' => (isset(Basic::$parameter['frame_name'])) ? Basic::$parameter['frame_name'] : 'kitframework_iframe',
            'add' => (isset(Basic::$parameter['frame_add'])) ? Basic::$parameter['frame_add'] : 30,
            'width' => (isset(Basic::$parameter['frame_width'])) ? Basic::$parameter['frame_width'] : '100%',
            'height' => (isset(Basic::$parameter['frame_height'])) ? Basic::$parameter['frame_height'] : '400px',
            'auto' => (isset(Basic::$parameter['frame_auto']) && ((Basic::$parameter['frame_auto'] == 'false') || (Basic::$parameter['frame_auto'] == '0'))) ? false : true,
            'source' => (isset(Basic::$parameter['frame_source'])) ? Basic::$parameter['frame_source'] : ''
        );
    }

    public function getParameters()
    {
        return self::$cms_parameters;
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

    /**
     * Create a iFrame for embedding a kitCommand within a Content Management System
     *
     * @param string $source the content URL of the iFrame
     */
    public function createIFrame($source)
    {
        self::$frame['source'] = $source;
        return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'kitcommand.iframe.twig', self::$preferred_template),
            array(
                'frame' => self::$frame
            ));
    }

}
