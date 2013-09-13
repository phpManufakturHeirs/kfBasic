<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control;

use Twig_Extension;
use Twig_SimpleFunction;
use Silex\Application;

require_once MANUFAKTUR_PATH . '/Basic/Control/twigFunction.php';

/**
 * The Twig extension class for the kitFramework
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class twigExtension extends Twig_Extension
{

    protected $app = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct (Application $app)
    {
        $this->app = $app;
    }

    /**
     *
     * @see Twig_ExtensionInterface::getName()
     */
    public function getName ()
    {
        return 'kitFramework';
    } // getName()

    /**
     *
     * @see Twig_Extension::getGlobals()
     */
    public function getGlobals ()
    {
        return array(
            'FRAMEWORK_URL' => FRAMEWORK_URL,
            'FRAMEWORK_TEMPLATE_URL' => FRAMEWORK_TEMPLATE_URL,
            'FRAMEWORK_MEDIA_URL' => FRAMEWORK_MEDIA_URL,
            'FRAMEWORK_MEDIA_PROTECTED_URL' => FRAMEWORK_MEDIA_PROTECTED_URL,
            'CMS_TEMPLATE_URL' => CMS_TEMPLATE_URL,
            'CMS_URL' => CMS_URL,
            'CMS_MEDIA_URL' => CMS_MEDIA_URL,
            'CMS_ADMIN_URL' => CMS_ADMIN_URL,
            'CMS_TYPE' => CMS_TYPE,
            'CMS_VERSION' => CMS_VERSION,
            'MANUFAKTUR_URL' => MANUFAKTUR_URL,
            'THIRDPARTY_URL' => THIRDPARTY_URL
        );
    } // getGlobals()

    /**
     *
     * @see Twig_Extension::getFunctions()
     */
    public function getFunctions ()
    {
        return array(
            new Twig_SimpleFunction('is_authenticated', 'twig_is_authenticated'),
            new Twig_SimpleFunction('user_display_name', 'twig_user_display_name'),
            new Twig_SimpleFunction('template_file', 'twig_template_file'),
            new Twig_SimpleFunction('parse_command', 'twig_parse_command'),
            new Twig_SimpleFunction('command', 'twig_exec_command'),
            new Twig_SimpleFunction('recaptcha', 'twig_recaptcha'),
            new Twig_SimpleFunction('recaptcha_is_active', 'twig_recaptcha_is_active')
        );
    } // getFunctions()

} // class twigExtension

