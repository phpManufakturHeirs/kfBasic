<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\Setup;

use Silex\Application;
use phpManufaktur\Basic\Data\Security\Users;
use phpManufaktur\Basic\Data\ExtensionCatalog;
use phpManufaktur\Basic\Control\ExtensionCatalog as ExtensionCatalogControl;
use phpManufaktur\Basic\Data\Setting;
use phpManufaktur\Basic\Data\ExtensionRegister;
use phpManufaktur\Basic\Data\kitCommandParameter;
use phpManufaktur\Basic\Control\CMS\InstallSearch;
use phpManufaktur\Basic\Data\Security\AdminAction;
use phpManufaktur\Updater\Updater;
use phpManufaktur\Basic\Data\i18n\i18nScanFile;
use phpManufaktur\Basic\Data\i18n\i18nSource;
use phpManufaktur\Basic\Data\i18n\i18nReference;
use phpManufaktur\Basic\Data\i18n\i18nTranslation;
use phpManufaktur\Basic\Data\i18n\i18nTranslationFile;
use phpManufaktur\Basic\Data\i18n\i18nTranslationUnassigned;

/**
 * Setup all needed database tables and initialize the kitFramework
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class Setup
{
    protected $app = null;

    /**
     * Release 0.42
     */
    protected function release_042()
    {
        $Update = new Update();
        $Update->release_042($this->app);
    }

    /**
     * Check the namespaces autoloader to enable the LIBRARY functions
     *
     * @param Application $app
     */
    public function checkAutoloadNamespaces(Application $app)
    {
        // check the autoload_namespaces
        $framework_version = file_get_contents(FRAMEWORK_PATH.'/VERSION');
        if (version_compare($framework_version, '0.27', '<=') &&
            !$app['filesystem']->exists(FRAMEWORK_PATH.'/framework/composer/autoload_namespaces.php.bak')) {
            $app['filesystem']->copy(
                FRAMEWORK_PATH.'/framework/composer/autoload_namespaces.php',
                FRAMEWORK_PATH.'/framework/composer/autoload_namespaces.php.bak'
            );
            $app['filesystem']->copy(
                MANUFAKTUR_PATH.'/Basic/Data/Setup/Files/Release_0.69/framework/composer/autoload_namespaces.php',
                FRAMEWORK_PATH.'/framework/composer/autoload_namespaces.php'
            );
            $app['monolog']->addDebug('Replaced /framework/composer/autoload_namespace.php to enable autoloading from the LIBRARY');
        }
    }

    /**
     * Create the database tables for the BASIC extension of the kitFramework
     *
     * @param Application $app
     */
    public function exec(Application $app)
    {
        $this->app = $app;

        // check the autoload namespaces
        $this->checkAutoloadNamespaces($app);

        // create the framework user table
        $users = new Users($app);
        $users->createTable();

        // create the Extension Catalog
        $catalog = new ExtensionCatalog($app);
        $catalog->createTable();

        // create the setting table
        $setting = new Setting($app);
        $setting->createTable();
        $setting->insertDefaultValues();

        // create the table for the extension register
        $register = new ExtensionRegister($app);
        $register->createTable();

        // create the table for the kitCommand parameters
        $cmdParameter = new kitCommandParameter($app);
        $cmdParameter->createTable();

        // create the AdminAction table
        $adminAction = new AdminAction($app);
        $adminAction->createTable();

        // maybe BASIC is installed by an older kitFrameworkCMSTool ...
        $this->release_042();

        // install the search function
        $Search = new InstallSearch($app);
        $Search->exec();

        // We need the online catalog for further actions
        $ExtensionCatalogControl = new ExtensionCatalogControl($app);
        $ExtensionCatalogControl->getOnlineCatalog();
        $app['monolog']->addDebug('[BASIC Setup] Got the online catalog from Github');

        If (!$app['filesystem']->exists(MANUFAKTUR_PATH.'/Library')) {
            // missing the library
            if (false !== ($extension = $catalog->selectByGroupAndName('phpManufaktur', 'Library'))) {
                // grant that the updater is installed in the separated directory and is actual
                if (!file_exists(MANUFAKTUR_PATH.'/Updater')) {
                    $app['filesystem']->mkdir(MANUFAKTUR_PATH.'/Updater');
                }
                $app['filesystem']->copy(MANUFAKTUR_PATH.'/Basic/Control/Updater/Updater.php', MANUFAKTUR_PATH.'/Updater/Updater.php', true);

                $Updater = new Updater();
                // install the Library
                $Updater->controllerInstallExtension($app, $extension['id'], false);
                $Updater->clearAlert();
            }
        }

        // create the tables for the localeEditor
        $i18nScanFile = new i18nScanFile($app);
        $i18nScanFile->createTable();

        $i18nSource = new i18nSource($app);
        $i18nSource->createTable();

        $i18nReference = new i18nReference($app);
        $i18nReference->createTable();

        $i18nTranslation = new i18nTranslation($app);
        $i18nTranslation->createTable();

        $i18nTranslationFile = new i18nTranslationFile($app);
        $i18nTranslationFile->createTable();

        $i18nTranslationUnassigned = new i18nTranslationUnassigned($app);
        $i18nTranslationUnassigned->createTable();

        return $app['translator']->trans('Successfull installed the extension %extension%.',
            array('%extension%' => 'Basic'));
    }

}
