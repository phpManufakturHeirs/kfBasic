<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data;

use Silex\Application;

/**
 * Data table for the extension catalog for the kitFramework
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class localeTranslation
{
    protected $app = null;
    protected static $table_name = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'basic_locale_translation';
    }

    /**
     * Create the table 'basic_locale_reference'
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $table_source = FRAMEWORK_TABLE_PREFIX.'basic_locale_source';
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `translation_id` INT(11) NOT NULL AUTO_INCREMENT,
      `locale_id` INT(11) NOT NULL DEFAULT -1,
      `locale_locale` VARCHAR(2) NOT NULL DEFAULT 'EN',
      `locale_source` TEXT NOT NULL,
      `locale_md5` VARCHAR(64) NOT NULL DEFAULT '',
      `translation_text` TEXT NOT NULL,
      `translation_md5` VARCHAR(64) NOT NULL DEFAULT '',
      `translation_remark` TEXT NOT NULL,
      `translation_status` ENUM ('PENDING', 'TRANSLATED', 'CONFLICT', 'WIDOWED') NOT NULL DEFAULT 'PENDING',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`translation_id`),
      INDEX (`locale_id`),
      CONSTRAINT
        FOREIGN KEY (`locale_id`)
        REFERENCES `$table_source` (`locale_id`)
        ON DELETE CASCADE
    )
    COMMENT='Locale Translations'
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
     * Check if the given locale ID exists for the language.
     *
     * @param unknown $locale_id
     * @throws \Exception
     * @return boolean
     */
    public function existsLocaleID($locale_id, $locale)
    {
        try {
            $SQL = "SELECT `locale_id` FROM `".self::$table_name."` WHERE `locale_id`=$locale_id AND `locale_locale`='$locale'";
            $id = $this->app['db']->fetchColumn($SQL);
            return ($id > 0);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Insert a new translation record and return the last inserted ID
     *
     * @param array $data
     * @throws \Exception
     */
    public function insert($data)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                $insert[$key] = (is_string($value)) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $checks = array('locale_source', 'translation_text', 'translation_remark');
            foreach ($checks as $check) {
                if (!isset($insert[$key])) {
                    $insert[$key] = '';
                }
            }
            if (empty($insert)) {
                throw new \Exception('The received data record is empty!');
            }
            $this->app['db']->insert(self::$table_name, $insert);
            return $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Get widowed translations which are no longer referenced in the sources
     *
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function selectWidowed()
    {
        try {
            $SQL = "SELECT `locale_id` FROM `".self::$table_name."` WHERE `locale_id` ".
                "NOT IN (SELECT `locale_id` FROM `".FRAMEWORK_TABLE_PREFIX."basic_locale_source`)";
            $widowed = $this->app['db']->fetchAll($SQL);
            return (!empty($widowed)) ? $widowed : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete all records with the given locale ID
     *
     * @param integer $locale_id
     * @throws \Exception
     */
    public function deleteLocaleID($locale_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('locale_id' => $locale_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Delete the record with the given translation ID
     *
     * @param integer $translation_id
     * @throws \Exception
     */
    public function delete($translation_id)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('translation_id' => $translation_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if the given checksum for the locale exists. Return the translation ID or FALSE
     *
     * @param string $md5
     * @param string $locale
     * @throws \Exception
     * @return Ambigous <boolean, unknown>
     */
    public function existsMD5($md5, $locale)
    {
        try {
            $SQL = "SELECT `translation_id` FROM `".self::$table_name."` WHERE `locale_md5`='$md5' AND `locale_locale`='$locale'";
            $locale_id = $this->app['db']->fetchColumn($SQL);
            return ($locale_id > 0) ? $locale_id : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Select the record for the given translation ID. Return FALSE if record not exists.
     *
     * @param integer $translation_id
     * @throws \Exception
     * @return Ambigous <boolean, array>
     */
    public function select($translation_id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `translation_id`=$translation_id";
            $result = $this->app['db']->fetchAssoc($SQL);
            $translation = array();
            if (is_array($result)) {
                foreach ($result as $key => $value) {
                    $translation[$key] = is_string($value) ? $this->app['utils']->unsanitizeText($value) : $value;
                }
            }
            return (!empty($translation)) ? $translation : false;
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Update the record for the given translation ID
     *
     * @param integer $translation_id
     * @param array $data
     * @throws \Exception
     */
    public function update($translation_id, $data)
    {
        try {
            $update = array();
            foreach ($data as $key => $value) {
                $update[$key] = is_string($value) ? $this->app['utils']->sanitizeText($value) : $value;
            }
            $this->app['db']->update(self::$table_name, $update, array('translation_id' => $translation_id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
