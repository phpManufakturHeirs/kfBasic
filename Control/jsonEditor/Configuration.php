<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\jsonEditor;

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
        self::$config_path = MANUFAKTUR_PATH.'/Basic/config.jsoneditor.json';
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
            'last_scan' => null,
            'wait_hours' => 72,
            'exclude' => array(
                'file' => array(
                    'composer.json',
                    'package.json',
                    'bower.json',
                    'extension.*',
                    'command.*',
                    'filter.*'
                ),
                'directory' => array(
                    'Data',
                    'Library/Library'
                )
            ),
            'help' => array(
                'accounts.list.json' => array(
                    'en' => '<p>This file enable you to change the visible columns and the order of the fields shown in the <a href="{FRAMEWORK_URL}/admin/accounts/list" target="_blank">Account Overview List</a>.</p><p>Available fields for the usage in <var>columns</var> and <var>list > order > by</var> are: <var>id, username, email, password, displayname, last_login, roles, guid, guid_timestamp, guid_status, status</var> and <var>timestamp</var>. With <var>list > rows_per_page</var> you determine how many accounts will be shown per page.</p>',
                    'de' => '<p>Diese Datei ermöglicht es Ihnen die angezeigten Spalten und die Sortierung der Felder in der <a href="{FRAMEWORK_URL}/admin/accounts/list" target="_blank">Übersicht der Benutzerkonten</a> zu ändern.</p><p>Verfügbare Felder für die Verwendung in <var>columns</var> und <var>list > order > by</var> sind: <var>id, username, email, password, displayname, last_login, roles, guid, guid_timestamp, guid_status, status</var> und <var>timestamp</var>. Mit <var>list > rows_per_page</var> legen Sie fest, wie viele Benutzerkonten pro Seite angezeigt werden.</p>'
                ),
                'cms.json' => array(
                    'en' => '<p>This configuration file contains information about the parent Content Management System (CMS). If you have moved your website to another URL or have changed the hosting directory you should adapt the settings in this file.</p><p>The <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#cmsjson" target="_blank">kitFramework WIKI</a> describe the settings for the <var>cms.json</var>.<p>',
                    'de' => '<p>Diese Konfigurationsdatei enthält Informationen zu dem übergeordneten Content Management System (CMS). Falls Sie die URL der Website verändert haben oder sich das Stammverzeichnis auf dem Webserver geändert hat, sollten Sie die Einstellungen in dieser Datei prüfen und anpassen.</p><p>Das <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#cmsjson" target="_blank">kitFramework WIKI</a> erläutert Ihnen alle Einstellungsmöglichkeiten für die <var>cms.json</var>.</p>'
                ),
                'config.jsoneditor.json' => array(
                    'en' => '<p>This is the configuration file for the configuration editor you are using just in this moment.</p><p>Normally you should not change anything in this configuration. The file contains the help information for the different configuration files and the list of the available configuration files to avoid scanning the system each time.</p><p>If you are missing a configuration file, i.e. for a just installed extension, please use the <key>Rescan</key> button above to force a new scan.</p>',
                    'de' => '<p>Diese Konfigurationsdatei enthält die Einstellungen für den Konfigurations Editor, den Sie just in diesem Moment verwenden.</p><p>Normalerweise ist es nicht erforderlich an diesen Einstellungen etwas zu ändern. Die Datei enthält die Hilfeinformationen, die zu den einzelnen Konfigurationsdateien angezeigt werden sowie die Liste der verfügbaren Konfigurationsdateien um ein Überprüfen des Systems bei jedem Aufruf des Editors zu verhindern.</p><p>Falls Sie eine Konfigurationsdatei vermissen, z.B. für eine gerade erst installierte Erweiterung, verwenden Sie bitte den <key>Suchlauf</key> Schalter um ein erneutes Durchsuchen des Systems zu erzwingen.</p>'
                ),
                'doctrine.cms.json' => array(
                    'en' => '<p>This configuration file contains the settings for the database connect. If you have changed the database settings for the parent Content Management System (CMS) you must also adapt the database settings in this file, otherwise the kitFramework will no longer work.</p><p>The <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#doctrinecmsjson" target="_blank">kitFramework WIKI</a> describe the settings for the <var>doctrine.cms.json</var>.</p>',
                    'de' => '<p>Diese Konfigurationsdatei enthält die Datenbankeinstellungen. Wenn Sie die Datenbankeinstellungen für das übergeordnete Content Management System (CMS) ändern, müssen Sie die Einstellungen in dieser Datei ebenfalls anpassen - andernfalls wird das kitFramework nicht mehr korrekt funktionieren.</p><p>Das <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#doctrinecmsjson" target="_blank">kitFramework WIKI</a> erläutert Ihnen alle Einstellungsmöglichkeiten für die <var>doctrine.cms.json</var>.</p>'
                ),
                'framework.json' => array(
                    'en' => '<p>This is the main configuration file for the kitFramework. Here you can switch on and off the <var>DEBUG</var> and <var>CACHE</var> mode and advice the kitFramework to load your user defined templates before the <var>default</var> templates.</p><p>The <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration" target="_blank">kitFramework WIKI</a> describe the settings for the <var>framework.json</var>.</p>',
                    'de' => '<p>Dies ist die zentrale Konfigurationsdatei für das kitFramework. Hier können Sie den <var>DEBUG</var> und <var>CACHE</var> Modus ein- oder ausschalten und das kitFramework anweisen stets Ihre benutzerdefinierten Templates vor den Standardvorlagen zu laden.</p><p>Das <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration" target="_blank">kitFramework WIKI</a> erläutert Ihnen alle Einstellungsmöglichkeiten für die <var>framework.json</var>.</p>'
                ),
                'proxy.json' => array(
                    'en' => '<p>If you are using a proxy server you will need this configuration file. Please ask your system administrator for the needed settings.</p>',
                    'de' => '<p>Falls Sie einen Proxy Server verwenden benötigen Sie diese Konfigurationsdatei. Bitte fragen Sie Ihren Systemadministrator nach den benötigten Einstellungen.</p>'
                ),
                'swift.cms.json' => array(
                    'en' => '<p>This file is needed to configure the email settings for the kitFramework. Please ask your email provider for the needed SMTP server, port, username and password.</p><p>You can check the email settings and <a href="{FRAMEWORK_URL}/admin/test/mail" target="_blank">send a testmail.</p><p>The <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#swiftcmsjson" target="_blank">kitFramework WIKI</a> describe the settings for the <var>swift.cms.json</var>.</p>',
                    'de' => '<p>Diese Konfigurationsdatei wird benötigt um die E-Mail Einstellungen für das kitFramework festzulegen. Bitte fragen Sie Ihren E-Mail Anbieter nach den benötigten Einstellungen für den SMTP Server, Port, Benutzername und Passwort.</p><p>Sie können die E-Mail Einstellungen überprüfen, in dem Sie eine <a href="{FRAMEWORK_URL}/admin/test/mail" target="_blank">Testmail versenden</a>.</p><p>Das <a href="https://github.com/phpManufaktur/kitFramework/wiki/kitFramework-%23-Configuration#swiftcmsjson" target="_blank">kitFramework WIKI</a> erläutert Ihnen alle Einstellungsmöglichkeiten für die <var>swift.cms.json</var>.</p>'
                )
            ),
            'configuration_files' => array()
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
