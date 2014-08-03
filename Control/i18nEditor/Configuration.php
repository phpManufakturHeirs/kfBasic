<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\i18nEditor;

use Silex\Application;

class Configuration
{
    protected $app = null;
    protected static $config = null;
    protected static $config_path = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$config_path = MANUFAKTUR_PATH.'/Basic/config.i18n.editor.json';
        $this->readConfiguration();
    }

    /**
     * Return the default configuration array
     *
     * @return array
     */
    public function getDefaultConfigArray()
    {
        return array(
            'developer_mode' => false,
            'parse' => array(
                'php' => array(
                    'stop_word' => array(
                        "'choice'",
                        "'hidden'",
                        "'text'",
                        "'textarea'",
                        "'utils'"
                    ),
                    'start_word' => array(
                        'add',
                        'setAlert',
                        'trans'
                    ),
                    'property_word' => array(
                        "'empty_value'",
                        "'label'",
                        "'hint'",
                        "'accounts.list.json'",
                        "'cms.json'",
                        "'config.jsoneditor.json'",
                        "'doctrine.cms.json'",
                        "'framework.json'",
                        "'proxy.json'",
                        "'swift.cms.json'"
                    )
                ),
                'twig' => array(
                    'regex' => array(
                        '/{{([^}]+)\|(?:transchoice\((.*?)\)|transchoice|trans|trans\((.*?)\))(?:\|([^}]+)|\s*)}}/i',
                        '/content\s{0,1}:(.*?)\|trans/i'
                    )
                )
            ),
            'finder' => array(
                'php' => array(
                    'exclude' => array(
                        'file' => array(

                        ),
                        'directory' => array(
                            'Control/CMS',
                            'Control/cURL',
                            'Control/gitHub',
                            'Control/kitSearch',
                            'Control/unZip',
                            'Control/utf-8',
                            'Data/Locale',
                            'Template',
                            'Library'
                        )
                    )
                ),
                'twig' => array(
                    'exclude' => array(
                        'file' => array(

                        ),
                        'directory' => array(
                            'Control',
                            'Data',
                            'Library'
                        )
                    ),
                    'template' => array(
                        'name' => array(
                            'exclude' => array(
                                'TemplateTools'
                            )
                        ),
                        'use_subdirectory' => array(
                            'CommandCollection'
                        )
                    )
                ),
                'locale' => array(
                    'exclude' => array(
                        'file' => array(

                        ),
                        'directory' => array(
                            'Control',
                            'Template',
                            'Library',
                            'Data/Setup',
                            'Data/CMS',
                            'Data/Security',
                            'TemplateTools'
                        )
                    )
                )
            ),
            'translation' => array(
                'locale' => array(
                    'DE',
                    'EN'
                ),
                'system' => array(
                    'Address billing',
                    'Address billing city',
                    'Address billing country code',
                    'Address billing street',
                    'Address billing zip',
                    'Address delivery',
                    'Address delivery country code',
                    'Admin',
                    'April',
                    'Archived',
                    'AT',
                    'August',
                    'Bad credentials',
                    'baron',
                    'captcha-timeout',
                    'CH',
                    'commercial use only',
                    'Communication cell',
                    'Communication email',
                    'Communication fax',
                    'Communication phone',
                    'Communication url',
                    'Configuration',
                    'Contact settings',
                    'Contact since',
                    'Contact timestamp',
                    'CURRENCY_SYMBOL',
                    'Customer',
                    'DATE_FORMAT',
                    'DATETIME_FORMAT',
                    'DE',
                    'December',
                    'DECIMAL_SEPARATOR',
                    'doc',
                    'doctor',
                    'EN',
                    'earl',
                    'February',
                    'Female',
                    'FR',
                    'Friday',
                    "I'm a sample header",
                    'I accept that this software is provided under <a href="http://opensource.org/licenses/MIT" target="_blank">MIT License</a>.',
                    'incorrect-captcha-sol',
                    'Insufficient user role',
                    'Intern',
                    'invalid-request-cookie',
                    'invalid-site-private-key',
                    'January',
                    'July',
                    'June',
                    'Male',
                    'March',
                    'May',
                    'Merchant',
                    'Monday',
                    'Nick name',
                    'NL',
                    'Note content',
                    'November',
                    "o'clock",
                    'October',
                    'Organization',
                    'Person nick name',
                    'prof',
                    'professor',
                    'Public',
                    'Saturday',
                    'September',
                    'Stay in touch, read our newsletter!',
                    'Sunday',
                    'TIME_FORMAT',
                    'This is a sample panel text whith some unnecessary content',
                    "This Tag type is created by the kitCommand 'Comments' and will be set for persons who leave a comment.",
                    'This value is not a valid email address.',
                    'THOUSAND_SEPARATOR',
                    'Thursday',
                    'Tuesday',
                    'Unchecked',
                    'Wednesday',
                    'Weekday',
                    'Weekdays'
                )
            ),
            'editor' => array(
                'sources' => array(
                    'list' => array(
                        'order_by' => 'locale_id',
                        'order_direction' => 'ASC'
                    )
                )
            )
        );
    }

    /**
     * Read the configuration file
     */
    protected function readConfiguration()
    {
        if (!$this->app['filesystem']->exists(self::$config_path)) {
            self::$config = $this->getDefaultConfigArray();
            $this->saveConfiguration();
        }
        self::$config = $this->app['utils']->readConfiguration(self::$config_path);
    }

    /**
     * Save the configuration file
     */
    public function saveConfiguration()
    {
        // write the formatted config file to the path
        file_put_contents(self::$config_path, $this->app['utils']->JSONFormat(self::$config));
        $this->app['monolog']->addDebug('Save configuration to '.basename(self::$config_path));
    }

    /**
     * Get the configuration array
     *
     * @return array
     */
    public function getConfiguration()
    {
        return self::$config;
    }

    /**
     * Set the configuration array
     *
     * @param array $config
     */
    public function setConfiguration($config)
    {
        self::$config = $config;
    }

}
