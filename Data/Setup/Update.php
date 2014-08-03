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
use phpManufaktur\Basic\Control\CMS\InstallSearch;
use phpManufaktur\Basic\Data\Security\AdminAction;
use Symfony\Component\Filesystem\Exception\IOException;
use phpManufaktur\Basic\Control\CMS\InstallAdminTool;
use phpManufaktur\Basic\Control\jsonEditor\Configuration;
use phpManufaktur\Basic\Data\i18n\i18nScanFile;
use phpManufaktur\Basic\Data\i18n\i18nSource;
use phpManufaktur\Basic\Data\i18n\i18nReference;
use phpManufaktur\Basic\Data\i18n\i18nTranslation;
use phpManufaktur\Basic\Data\i18n\i18nTranslationFile;
use phpManufaktur\Basic\Data\i18n\i18nTranslationUnassigned;

class Update
{
    protected $app = null;

    /**
     * Check if the give column exists in the table
     *
     * @param string $table
     * @param string $column_name
     * @return boolean
     */
    protected function columnExists($table, $column_name)
    {
        try {
            $query = $this->app['db']->query("DESCRIBE `$table`");
            while (false !== ($row = $query->fetch())) {
                if ($row['Field'] == $column_name) return true;
            }
            return false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Release 0.36
     */
    protected function release_036()
    {
        if (!$this->columnExists(FRAMEWORK_TABLE_PREFIX.'basic_extension_catalog', 'release_status')) {
            // add release_status
            $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."basic_extension_catalog` ADD `release_status` VARCHAR(64) NOT NULL DEFAULT 'undefined' AFTER `release`";
            $this->app['db']->query($SQL);
            $this->app['monolog']->addDebug('[BASIC Update] Add field `release_status` to table `basic_extension_catalog`');
        }

        if (!$this->columnExists(FRAMEWORK_TABLE_PREFIX.'basic_extension_register', 'release_status')) {
            // add field release_status
            $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."basic_extension_register` ADD `release_status` VARCHAR(64) NOT NULL DEFAULT 'undefined' AFTER `release`";
            $this->app['db']->query($SQL);
            $this->app['monolog']->addDebug('[BASIC Update] Add field `release_status` to table `basic_extension_register`');
        }
    }

    /**
     * Release 0.42
     */
    public function release_042(Application $app)
    {
        if (!file_exists(CMS_PATH.'/modules/kit_framework_search/VERSION')) {
            // the VERSION file exists since kitframework_search 0.11
            $app['filesystem']->copy(
                MANUFAKTUR_PATH.'/Basic/Data/Setup/Files/Release_0.42/search.php',
                CMS_PATH.'/modules/kit_framework_search/search.php',
                true);
            file_put_contents(CMS_PATH.'/modules/kit_framework_search/VERSION', '0.10.1');
            $app['monolog']->addDebug('BASIC Update] Changed kit_framework_search and added VERSION file');
        }
        if (file_exists(CMS_PATH.'/modules/kit_framework/VERSION')) {
            if (false === ($version = trim(file_get_contents(CMS_PATH.'/modules/kit_framework/VERSION')))) {
                throw new \Exception('Missing kit_framework VERSION file!');
            }
            if (version_compare('0.30', trim($version), '<=')) {
                // update the output filter for LEPTON
                $app['filesystem']->copy(
                    MANUFAKTUR_PATH.'/Basic/Data/Setup/Files/Release_0.42/output_interface.php',
                    CMS_PATH.'/modules/kit_framework/output_interface.php',
                    true);
                // update the output filter for BlackCat
                $app['filesystem']->copy(
                    MANUFAKTUR_PATH.'/Basic/Data/Setup/Files/Release_0.42/filter/kitCommands.php',
                    CMS_PATH.'/modules/kit_framework/filter/kitCommands.php',
                    true);
            }
        }
    }

    /**
     * Release 0.54
     */
    protected function release_054()
    {
        // create the AdminAction table
        $adminAction = new AdminAction($this->app);
        $adminAction->createTable();
    }

    /**
     * Release 0.63
     */
    protected function release_063()
    {
        $version = file_get_contents(CMS_PATH.'/modules/kit_framework/VERSION');
        if (version_compare(trim($version), '0.36', '<=')) {
            // copy a new tool.php to the CMS
            $this->app['filesystem']->copy(
                MANUFAKTUR_PATH.'/Basic/Data/Setup/Files/Release_0.63/tool.php',
                CMS_PATH.'/modules/kit_framework/tool.php',
                true);
        }
    }

    /**
     * Release 0.69
     */
    protected function release_069()
    {
        $Setup = new Setup();
        $Setup->checkAutoloadNamespaces($this->app);
    }

    /**
     * Release 0.72
     */
    protected function release_072()
    {
        if (!$this->columnExists(FRAMEWORK_TABLE_PREFIX.'basic_users', 'status')) {
            $SQL = "ALTER TABLE `".FRAMEWORK_TABLE_PREFIX."basic_users` ADD `status` ENUM ('ACTIVE','LOCKED') NOT NULL DEFAULT 'ACTIVE' AFTER `guid_status`";
            $this->app['db']->query($SQL);
            $this->app['monolog']->addDebug('[BASIC Update] Add field `status` to table `basic_users`');
        }
        $files = array(
            '/Basic/Control/Account.php',
            '/Basic/Control/forgottenPassword.php',
            '/Basic/Control/Goodbye.php',
            '/Basic/Control/Login.php',
            '/Basic/Control/manufakturPasswordEncoder.php',
            '/Basic/Control/UserProvider.php',
            '/Basic/Template/default/framework/admins.only.twig',
            '/Basic/Template/default/framework/body.message.twig'
        );
        foreach ($files as $file) {
            // remove no longer needed directories and files
            if ($this->app['filesystem']->exists(MANUFAKTUR_PATH.$file)) {
                try {
                    $this->app['filesystem']->remove(MANUFAKTUR_PATH.$file);
                    $this->app['monolog']->addDebug(sprintf('[BASIC Update] Removed file or directory %s', $file));
                } catch (IOException $e) {
                    $this->app['monolog']->addDebug($e->getMessage());
                }
            }
        }
    }

    /**
     * Release 0.76
     */
    protected function release_076()
    {
        if (!$this->app['filesystem']->exists(CMS_PATH.'/modules/kit_framework/framework_info.php')) {
            $this->app['filesystem']->copy(
                MANUFAKTUR_PATH.'/Basic/Data/Setup/Files/Release_0.76/framework_info.php',
                CMS_PATH.'/modules/kit_framework/framework_info.php');
            $this->app['monolog']->addDebug(sprintf('[BASIC Update] Copy file %s to %s',
                'framework_info.php', CMS_PATH.'/modules/kit_framework/'));
        }
    }

    /**
     * Release 0.84
     */
    protected function release_084()
    {
        try {
            // /logfile/kit2.log and /logfile/kit2.log.bak are no longer used
            $this->app['filesystem']->remove(FRAMEWORK_PATH.'/logfile/kit2.log');
            $this->app['filesystem']->remove(FRAMEWORK_PATH.'/logfile/kit2.log.bak');
        } catch (IOException $e) {
            $this->app['monolog']->addDebug($e->getMessage());
        }
    }

    /**
     * Relese 0.94
     */
    protected function release_094()
    {
        $config = $this->app['utils']->readConfiguration(FRAMEWORK_PATH.'/config/framework.json');
        if (!isset($config['FRAMEWORK_UID'])) {
            // create a unique identifier for the framework
            $config['FRAMEWORK_UID'] = $this->app['utils']->createGUID();
            file_put_contents(FRAMEWORK_PATH.'/config/framework.json', $this->app['utils']->JSONFormat($config));
        }
    }

    /**
     * Release 0.95
     */
    protected function release_095()
    {
        if (!$this->app['filesystem']->exists(FRAMEWORK_MEDIA_PATH)) {
            $this->app['filesystem']->mkdir(FRAMEWORK_MEDIA_PATH);
        }
        if (!$this->app['filesystem']->exists(FRAMEWORK_MEDIA_PATH.'/cms')) {
            // try to create a symbolic link to the CMS MEDIA directory
            try {
                $this->app['filesystem']->symlink(CMS_MEDIA_PATH, FRAMEWORK_MEDIA_PATH.'/cms');
            } catch (IOException $e) {
                // symlink creation fails!
                $this->app['monolog']->addDebug($e->getMessage());
            }
        }
    }

    /**
     * Release 0.96
     */
    protected function release_096()
    {
        if ($this->app['filesystem']->exists(MANUFAKTUR_PATH.'/Basic/Control/kitCommand/kitCommand.php')) {
            try {
                $this->app['filesystem']->remove(MANUFAKTUR_PATH.'/Basic/Control/kitCommand/kitCommand.php');
            } catch (IOException $e) {
                $this->app['monolog']->addDebug($e->getMessage());
            }
        }
    }

    /**
     * Release 0.99
     */
    protected function release_099()
    {
        //if (!$this->app['filesystem']->exists(CMS_PATH.'/mo'))
        $admin_tool = new InstallAdminTool($this->app);
        $admin_tool->exec(MANUFAKTUR_PATH.'/Basic/extension.jsoneditor.json', '/basic/cms/jsoneditor');
    }

    /**
     * Release 1.0.1
     */
    protected function release_101()
    {
        $jsonConfiguration = new Configuration($this->app);
        $json_config = $jsonConfiguration->getConfiguration();
        if (isset($json_config['help']['framework.json']['en'])) {
            // remove the first generation settings
            $default_config = $jsonConfiguration->getDefaultConfigArray();
            $json_config['help'] = $default_config['help'];
            $jsonConfiguration->setConfiguration($json_config);
            $jsonConfiguration->saveConfiguration();
        }
    }

    /**
     * Release 1.0.5
     */
    protected function release_105()
    {
        // class JSONFormat is moved to /jsonEditor/jsonFormat
        if ($this->app['filesystem']->exists(MANUFAKTUR_PATH.'/Basic/Control/JSON')) {
            $this->app['filesystem']->remove(MANUFAKTUR_PATH.'/Basic/Control/JSON');
        }

        // create the tables for localeEditor
        if (!$this->app['db.utils']->tableExists(FRAMEWORK_TABLE_PREFIX.'basic_i18n_scan_file')) {
            $i18nScanFile = new i18nScanFile($this->app);
            $i18nScanFile->createTable();
        }
        if (!$this->app['db.utils']->tableExists(FRAMEWORK_TABLE_PREFIX.'basic_i18n_source')) {
            $i18nSource = new i18nSource($this->app);
            $i18nSource->createTable();
        }
        if (!$this->app['db.utils']->tableExists(FRAMEWORK_TABLE_PREFIX.'basic_i18n_reference')) {
            $i18nReference = new i18nReference($this->app);
            $i18nReference->createTable();
        }
        if (!$this->app['db.utils']->tableExists(FRAMEWORK_TABLE_PREFIX.'basic_i18n_translation')) {
            $i18nTranslation = new i18nTranslation($this->app);
            $i18nTranslation->createTable();
        }
        if (!$this->app['db.utils']->tableExists(FRAMEWORK_TABLE_PREFIX.'basic_i18n_translation_file')) {
            $i18nTranslationFile = new i18nTranslationFile($this->app);
            $i18nTranslationFile->createTable();
        }
        if (!$this->app['db.utils']->tableExists(FRAMEWORK_TABLE_PREFIX.'basic_i18n_translation_unassigned')) {
            $i18nTranslationUnassigned = new i18nTranslationUnassigned($this->app);
            $i18nTranslationUnassigned->createTable();
        }
    }

    /**
     * Update the database tables for the BASIC extension of the kitFramework
     *
     * @param Application $app
     */
    public function exec(Application $app)
    {
        $this->app = $app;

        $this->release_036();
        $this->release_042($app);
        $this->release_054();
        $this->release_063();
        $this->release_069();
        $this->release_072();
        $this->release_076();
        $this->release_084();
        $this->release_094();
        $this->release_095();
        $this->release_096();
        $this->release_099();
        $this->release_101();
        $this->release_105();

        // install the search function
        $Search = new InstallSearch($app);
        $Search->exec();

        return $app['translator']->trans('Successfull updated the extension %extension%.',
            array('%extension%' => 'Basic'));
    }

}
