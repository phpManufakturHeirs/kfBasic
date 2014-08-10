<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\i18n;

use Silex\Application;

/**
 * Data table for the extension catalog for the kitFramework
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class i18nTranslationUnassigned
{
    protected $app = null;
    protected static $table_name = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'basic_i18n_translation_unassigned';
    }

    /**
     * Create the table 'basic_locale_scan_file'
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `unassigned_id` INT(11) NOT NULL AUTO_INCREMENT,
      `file_path` TEXT NOT NULL,
      `extension` VARCHAR(64) NOT NULL DEFAULT '',
      `locale_locale` VARCHAR(2) NOT NULL DEFAULT 'EN',
      `locale_source` TEXT NOT NULL,
      `locale_source_plain` TEXT NOT NULL,
      `translation_text` TEXT NOT NULL,
      `translation_text_plain` TEXT NOT NULL,
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`unassigned_id`)
    )
    COMMENT='Unassigned locale sources for the i18nEditor',
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addDebug("Created table '".self::$table_name);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Drop Table
     */
    public function dropTable()
    {
        $this->app['db.utils']->dropTable(self::$table_name);
    }

    /**
     * Truncate the table
     */
    public function truncateTable()
    {
        $this->app['db.utils']->truncateTable(self::$table_name);
    }

    /**
     * Insert a new record
     */
    public function insert($data)
    {
        try {
            $data['locale_source_plain'] = isset($data['locale_source']) ? $this->app['utils']->specialCharsToAsciiChars(strip_tags($data['locale_source']), true) : '';
            $data['translation_text_plain'] = isset($data['translation_text']) ? $this->app['utils']->specialCharsToAsciiChars(strip_tags($data['translation_text']), true) : '';
            $insert = array();
            foreach ($data as $key => $value) {
                $insert[$key] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $data);
            return $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Count all records
     *
     * @throws \Exception
     */
    public function count()
    {
        try {
            $SQL = "SELECT COUNT(*) FROM `".self::$table_name."`";
            return $this->app['db']->fetchColumn($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select all unassigned records, ordered by `locale_source`.
     * Return FALSE if no records exists
     *
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectAll()
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` ORDER BY `locale_source_plain` ASC";
            $results = $this->app['db']->fetchAll($SQL);
            $unassigneds = array();
            if (is_array($results)) {
                foreach ($results as $result) {
                    $unassigned = array();
                    foreach ($result as $key => $value) {
                        $unassigned[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                        if ($key == 'file_path') {
                            $unassigned[$key] = realpath($value);
                        }
                    }
                    $unassigneds[] = $unassigned;
                }
            }
            return (!empty($unassigneds)) ? $unassigneds : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
