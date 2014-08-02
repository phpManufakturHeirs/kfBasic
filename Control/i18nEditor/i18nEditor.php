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

class i18nEditor extends i18nParser
{
    protected static $script_start = null;
    protected static $usage = null;
    protected static $usage_param = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\localeEditor\localeParser::initialize()
     */
    protected function initialize(Application $app)
    {
        // get the current timestamp
        self::$script_start = microtime(true);

        parent::initialize($app);

        $usage = $this->app['request']->get('usage');
        self::$usage = is_null($usage) ? 'framework' : $usage;
        self::$usage_param = (self::$usage != 'framework') ? '?usage='.self::$usage : '';

        // set the locale from the CMS locale
        if (self::$usage != 'framework') {
            $app['translator']->setLocale($app['session']->get('CMS_LOCALE', 'en'));
        }
    }

    /**
     * Create the toolbar for the dialogs
     *
     * @param string $active
     * @return array
     */
    public function getToolbar($active) {
        $toolbar = array();
        $tabs = array('overview', 'about');
        foreach ($tabs as $tab) {
            switch ($tab) {
                case 'about':
                    $toolbar[$tab] = array(
                        'name' => 'about',
                        'text' => $this->app['translator']->trans('About'),
                        'hint' => $this->app['translator']->trans('Information about the i18nEditor'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/about'.self::$usage_param,
                        'active' => ($active === 'about')
                    );
                    break;
                case 'overview':
                    $toolbar[$tab] = array(
                        'name' => 'overview',
                        'text' => $this->app['translator']->trans('Overview'),
                        'hint' => $this->app['translator']->trans('...'),
                        'link' => FRAMEWORK_URL.'/admin/i18n/editor/overview'.self::$usage_param,
                        'active' => ($active === 'overview')
                    );
                    break;
            }
        }
        return $toolbar;
    }


    /**
     * Show the about dialog for flexContent
     *
     * @return string rendered dialog
     */
    public function ControllerAbout(Application $app)
    {
        $this->initialize($app);

        $extension = $this->app['utils']->readJSON(MANUFAKTUR_PATH.'/Basic/extension.i18n.editor.json');

        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/about.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('about'),
                'extension' => $extension
            ));
    }

    /**
     * Show the overview dialog for the i18nEditor
     *
     * @return string \Twig_Template
     */
    protected function showOverview()
    {
        return $this->app['twig']->render($this->app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/i18n/overview.twig'),
            array(
                'usage' => self::$usage,
                'toolbar' => $this->getToolbar('overview'),
                'alert' => $this->getAlert(),
            ));
    }

    /**
     * Scan the complete kitFramework and update all i18n data tables
     * 
     * @param Application $app
     * @return string
     */
    public function ControllerScan(Application $app)
    {
        $this->initialize($app);

        return $this->showOverview();
    }

    /**
     * The general controller for the localeEditor
     *
     * @param Application $app
     * @return string
     */
    public function ControllerOverview(Application $app)
    {
        $this->initialize($app);
        return $this->showOverview();

        // process PHP files
        $this->findPHPfiles();

        if (false !== ($files = $this->i18nScanFile->selectRegistered('PHP'))) {
            foreach ($files as $file) {
                $this->parsePHPfile($file['file_path']);
            }
        }

        // process Twig files
        $this->findTwigFiles();

        if (false !== ($files = $this->i18nScanFile->selectRegistered('TWIG'))) {
            foreach ($files as $file) {
                $this->parseTwigFile($file['file_path']);
            }
        }

        // remove widowed locale sources from the database
        $widowed = $this->i18nSource->selectWidowed();
        if (is_array($widowed)) {
            foreach ($widowed as $widow) {
                if (!in_array($widow['locale_source'], self::$config['translation']['system'])) {
                    $this->i18nSource->delete($widow['locale_id']);
                    $this->setAlert('Deleted widowed locale source with the ID %id%.',
                        array('%id%' => $widow['locale_id']));
                }
            }
        }

        // update the translation table
        $this->updateTranslationTable();

        // find the locale files
        $this->findLocaleFiles();

        $result = $this->i18nScanFile->selectCount();
        echo "<pre>";
        print_r($result);
        echo "</pre>";

        echo "Laufzeit: ". sprintf('%01.2f', (microtime(true) - self::$script_start))."<br>";

        echo $this->getAlert();
        return __METHOD__;
    }
}
