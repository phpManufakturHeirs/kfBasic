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
    protected static $page = null;

    public function __construct(Application $app, $parameters)
    {
        $this->app = $app;
        Basic::$parameter_string = $parameters;
        Basic::$cms_parameters = json_decode(base64_decode($parameters), true);
        Basic::$cms = Basic::$cms_parameters['cms'];
        Basic::$parameter = Basic::$cms_parameters['params'];
        Basic::$GET = Basic::$cms_parameters['GET'];
        Basic::$POST = Basic::$cms_parameters['POST'];
        // set the locale from the CMS locale
        $app['translator']->setLocale(Basic::$cms['locale']);
        // check for the preferred template
        Basic::$preferred_template = (isset(Basic::$parameter['template'])) ? Basic::$parameter['template'] : '';
        // set the values for the frame
        Basic::$frame = array(
            'id' => (isset(Basic::$parameter['frame_id'])) ? Basic::$parameter['frame_id'] : 'kitframework_iframe',
            'name' => (isset(Basic::$parameter['frame_name'])) ? Basic::$parameter['frame_name'] : 'kitframework_iframe',
            'add' => (isset(Basic::$parameter['frame_add'])) ? Basic::$parameter['frame_add'] : 50,
            'width' => (isset(Basic::$parameter['frame_width'])) ? Basic::$parameter['frame_width'] : '100%',
            'height' => (isset(Basic::$parameter['frame_height'])) ? Basic::$parameter['frame_height'] : '400px',
            'auto' => (isset(Basic::$parameter['frame_auto']) && ((Basic::$parameter['frame_auto'] == 'false') || (Basic::$parameter['frame_auto'] == '0'))) ? false : true,
            'source' => (isset(Basic::$parameter['frame_source'])) ? Basic::$parameter['frame_source'] : '',
            'class' => (isset(Basic::$parameter['frame_class'])) ? Basic::$parameter['frame_class'] : 'kitcommand',
            'redirect' => array(
                'active' => (isset(Basic::$parameter['frame_redirect']) && ((strtolower(Basic::$parameter['frame_redirect']) == 'false') || (Basic::$parameter['frame_redirect'] == '0'))) ? false : true,
                'route' => (isset(Basic::$GET['redirect'])) ? Basic::$GET['redirect'] : ''
                ),
            'tracking' => (isset(Basic::$parameter['frame_tracking']) && ((strtolower(Basic::$parameter['frame_tracking']) == 'false') || (Basic::$parameter['frame_tracking'] == '0'))) ? false : true
        );
        $tracking = '';
        if (Basic::$frame['tracking'] && file_exists(FRAMEWORK_PATH.'/config/tracking.htt')) {
            // enable the tracking for the iframe
            $tracking = file_get_contents(FRAMEWORK_PATH.'/config/tracking.htt');
        }
        // set the values for the page
        Basic::$page = array(
            'title' => (isset(Basic::$parameter['frame_title'])) ? Basic::$parameter['frame_title'] : '',
            'description' => (isset(Basic::$parameter['frame_description'])) ? Basic::$parameter['frame_description'] : '',
            'keywords' => (isset(Basic::$parameter['frame_keywords'])) ? Basic::$parameter['frame_keywords'] : '',
            'robots' => (isset(Basic::$parameter['frame_robots'])) ? Basic::$parameter['frame_robots'] : 'index,follow',
            'charset' => (isset(Basic::$parameter['frame_charset'])) ? Basic::$parameter['frame_charset'] : 'UTF-8',
            'tracking' => $tracking
        );

    }

    /**
     * Get the collected BASIC settings for the template
     *
     * @return array basic settings
     */
    public function getBasicSettings()
    {
        return array(
            'message' => $this->getMessage(),
            'cms' => self::$cms,
            'frame' => self::$frame,
            'page' => self::$page
        );
    }

    /**
     * Return the complete parameter array given from the CMS
     *
     * @return array parameters
     */
    public function getAllParameters()
    {
        return Basic::$cms_parameters;
    }

    /**
     * @return the $message
     */
    public function getMessage()
    {
        return Basic::$message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message, $params=array())
    {
        Basic::$message .= $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'kitcommand.iframe.message.twig', self::$preferred_template),
            array(
                'message' => $this->app['translator']->trans($message, $params)
            ));
    }

    /**
     * Check if a message is active
     *
     * @return boolean
     */
    public function isMessage()
    {
        return !empty(Basic::$message);
    }

    /**
     * Clear the existing message(s)
     */
    public function clearMessage()
    {
        Basic::$message = '';
    }

    /**
     * Switch the redirection on/off if the iframe content is executed external
     *
     * @param bool $active
     */
    public function setRedirectActive($active)
    {
        Basic::$frame['redirect']['active'] = $active;
    }

    /**
     * Get the redirection setting
     *
     * @return bool
     */
    public function getRedirectActive()
    {
        return Basic::$frame['redirect']['active'];
    }

    /**
     * Set the redirect route if the content of the iframe is executed external.
     * The routing must be handled by the application, checking $_GET['redirect']
     *
     * @param string $route
     */
    public function setRedirectRoute($route)
    {
        Basic::$frame['redirect']['route'] = $route;
    }

    /**
     * Get the active redirect route
     *
     * @return string route
     */
    public function getRedirectRoute()
    {
        return Basic::$frame['redirect']['route'];
    }

    /**
     * Set the page title for the iframe content. Will be ignored if the title
     * isset by the kitCommand frame_title.
     *
     * @param string $title
     */
    public function setPageTitle($title)
    {
        if (empty(Basic::$page['title'])) {
            Basic::$page['title'] = $title;
        }
    }

    /**
     * Get the page title of the iframe content
     *
     * @return string title
     */
    public function getPageTitle()
    {
        return Basic::$page['title'];
    }

    /**
     * Set the page description for the iframe content. Will be ignored if the
     * description isset by the kitCommand frame_description.
     *
     * @param string $description
     */
    public function setPageDescription($description)
    {
        if (empty(Basic::$page['description'])) {
            Basic::$page['description'] = $description;
        }
    }

    /**
     * Get the page description of the iframe content
     *
     * @return string description
     */
    public function getPageDescription()
    {
        return Basic::$page['description'];
    }

    /**
     * Set the page keywords for the iframe content. Will be ignored if the
     * keywords are set by the kitCommand frame_keywords.
     *
     * @param string $keywords
     */
    public function setPageKeywords($keywords)
    {
        if (empty(Basic::$page['keywords'])) {
            Basic::$page['keywords'] = $keywords;
        }
    }

    /**
     * Get the page keywords of the iframe content
     *
     * @return string keywords
     */
    public function getPageKeywords()
    {
        return Basic::$page['keywords'];
    }

    /**
     * Set the ID for the iframe
     *
     * @param string $id
     */
    public function setFrameID($id)
    {
        Basic::$frame['id'] = $id;
    }

    /**
     * Get the ID of the iframe
     *
     * @return string
     */
    public function getFrameID()
    {
        return Basic::$frame['id'];
    }

    /**
     * Set the name of the iframe
     *
     * @param string $name
     */
    public function setFrameName($name)
    {
        Basic::$frame['name'] = $name;
    }

    /**
     * Get the name of the iframe
     *
     * @return string
     */
    public function getFrameName()
    {
        return Basic::$frame['name'];
    }

    /**
     * Set additional heigth for the iframe
     *
     * @param integer $add
     */
    public function setFrameAdd($add)
    {
        Basic::$frame['add'] = $add;
    }

    /**
     * Get the additional height for the iframe
     *
     * @return integer
     */
    public function getFrameAdd()
    {
        return Basic::$frame['add'];
    }

    /**
     * Set the iframe width
     *
     * @param <mixed> $width string with percent or pixel value
     */
    public function setFrameWidth($width)
    {
        Basic::$frame['width'] = $width;
    }

    /**
     * Get the iframe width
     *
     * @return string
     */
    public function getFrameWidth()
    {
        return Basic::$frame['width'];
    }

    /**
     * Set the iframe height as pixel value
     *
     * @param string $height
     */
    public function setFrameHeight($height)
    {
        Basic::$frame['height'] = $height;
    }

    /**
     * Get the iframe height value
     *
     * @return string
     */
    public function getFrameHeight()
    {
        return Basic::$frame['height'];
    }

    /**
     * Switch the iframe automatic height control on or off
     *
     * @param bool $auto
     */
    public function setFrameAuto($auto)
    {
        Basic::$frame['auto'] = $auto;
    }

    /**
     * Get the iframe automatic height control value
     *
     * @return bool
     */
    public function getFrameAuto()
    {
        return Basic::$frame['auto'];
    }

    /**
     * Set the class for the iframe itself
     *
     * @param string $class
     */
    public function setFrameClass($class)
    {
        Basic::$frame['class'] = $class;
    }

    /**
     * Get the class for the iframe
     *
     * @return string
     */
    public function getFrameClass()
    {
        return Basic::$frame['class'];
    }


    /**
     * Create a iFrame for embedding a kitCommand within a Content Management System
     *
     * @param string $source the content URL of the iFrame
     */
    public function createIFrame($source)
    {
        Basic::$frame['source'] = $source;
        return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'kitcommand.iframe.twig', self::$preferred_template),
            array(
                'frame' => Basic::$frame
            ));
    }

}
