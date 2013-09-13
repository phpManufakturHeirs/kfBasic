<?php

/**
 * kitFramework:Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\ReCaptcha;

use Silex\Application;

require_once MANUFAKTUR_PATH.'/Basic/Control/ReCaptcha/recaptchalib.php';

class ReCaptcha
{
    protected $app = null;
    protected static $is_enabled = false;
    protected static $is_active = true;
    protected static $config = null;
    protected static $public_key = null;
    protected static $private_key = null;
    protected static $last_error = null;
    protected static $use_ssl = false;
    protected static $theme = null;
    protected static $custom_theme_widget = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app) {
        $this->app = $app;
        $this->ReadConfigurationFile();
    }

    /**
     * Check if the ReCaptcha Service is enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return (bool) self::$is_enabled;
    }

    /**
     * Activate or deactivate the ReCaptcha Service
     *
     * @param boolean $boolean
     */
    public function Activate($boolean)
    {
        self::$is_active = (bool) $boolean;
    }

    /**
     * Check if ReCaptcha Service is active
     *
     * @return boolean
     */
    public function isActive()
    {
        return (bool) (self::$is_enabled && self::$is_active);
    }

    /**
     * Return the last ReCaptcha error or NULL
     *
     * @return NULL|string
     */
    public function getLastError()
    {
        return self::$last_error;
    }

    /**
     * Get the actual ReCaptcha theme
     *
     * @return string
     */
    public function getTheme()
    {
        return self::$theme;
    }

    /**
     * Set the theme for the ReCaptcha
     *
     * @link https://developers.google.com/recaptcha/docs/customization
     * @param string $theme
     */
    public function setTheme($theme)
    {
        self::$theme = $theme;
    }

    /**
     * Get the actual custom theme widget
     *
     * @return string
     */
    public function getCustomThemeWidget()
    {
        return self::$custom_theme_widget;
    }

    /**
     * Set a custom theme widget. You must set the theme to 'custom' to enable
     * this ReCaptcha feature
     *
     * @link https://developers.google.com/recaptcha/docs/customization
     * @param unknown $custom_theme_widget
     */
    public function setCustomThemeWidget($custom_theme_widget)
    {
        self::$custom_theme_widget = $custom_theme_widget;
    }

    /**
     * Read the configuration file /config/recaptcha.json.
     * Execute CreateConfigurationFile if the config file not exists
     *
     */
    protected function ReadConfigurationFile()
    {
        if (!file_exists(FRAMEWORK_PATH.'/config/recaptcha.json')) {
            $this->CreateConfigurationFile();
        }
        // read the config file
        self::$config = $this->app['utils']->ReadConfiguration(FRAMEWORK_PATH.'/config/recaptcha.json');
        // set the values
        self::$is_enabled = (isset(self::$config['enabled'])) ? self::$config['enabled'] : true;
        self::$private_key = (isset(self::$config['key']['private'])) ? self::$config['key']['private'] : null;
        self::$public_key = (isset(self::$config['key']['public'])) ? self::$config['key']['public'] : null;
        self::$use_ssl = (isset(self::$config['use_ssl'])) ? self::$config['use_ssl'] : false;
        self::$theme = (isset(self::$config['theme'])) ? self::$config['theme'] : 'red';
        self::$custom_theme_widget = (isset(self::$config['custom_theme_widget'])) ? self::$config['custom_theme_widget'] : '';

        if (is_null(self::$private_key) || is_null(self::$public_key)) {
            self::$is_enabled = false;
        }
    }

    /**
     * Create a /config/recaptcha.json with default values
     *
     */
    protected function CreateConfigurationFile()
    {
        $config = array(
            'enabled' => true,
            'key' => array(
                // global keys generated for repcaptcha.phpmanufaktur.de
                'public' => '6LctVdgSAAAAAAf0tjxxC2AGdppPV6l3Hxx54W-5',
                'private' => '6LctVdgSAAAAAL7Ff3D3k0qhnFLXn9FCShKEPoMh'
            ),
            'use_ssl' => false,
            'theme' => 'red',
            'custom_theme_widget' => ''
        );
        file_put_contents(FRAMEWORK_PATH.'/config/recaptcha.json', $this->app['utils']->JSONFormat($config));
    }

    /**
     * If the ReCaptcha Service is active, return the ReCaptcha dialog for
     * the usage with Twig
     *
     * @return string
     */
    public function getHTML()
    {
        if (!self::$is_enabled || !self::$is_active) {
            return '';
        }
        $theme = self::$theme;
        $custom_theme_widget = self::$custom_theme_widget;
        $captcha = ($theme == 'custom') ? '' : recaptcha_get_html(self::$public_key, self::$last_error, self::$use_ssl);
        $response = <<<EOD
        <script type="text/javascript">
            var RecaptchaOptions = {
                theme : '$theme',
                custom_theme_widget : '$custom_theme_widget'
            };
        </script>
        $captcha
EOD;
        return $response;
    }

    /**
     * Check if the submitted CAPTCHA is valid.
     * Return always TRUE if the Service is not enabled or inactive
     *
     * @return boolean
     */
    public function isValid()
    {
        if (!self::$is_enabled || !self::$is_active) {
            // ReCaptcha is not in use, return TRUE
            return true;
        }

        if (null !== ($response_field = $this->app['request']->get('recaptcha_response_field', null))) {
            // a ReCaptcha was submitted, so check the answer
            $response = recaptcha_check_answer(
                self::$private_key,
                $this->app['request']->getClientIP(), //instead of $_SERVER['REMOTE_ADDR'],
                $this->app['request']->get('recaptcha_challenge_field'),
                $this->app['request']->get('recaptcha_response_field'));
            if ($response->is_valid) {
                self::$last_error = null;
                return true;
            }
            else {
                self::$last_error = $response->error;
                return false;
            }
        }
        // in any other case return TRUE to keep the things running
        return true;
    }
}
