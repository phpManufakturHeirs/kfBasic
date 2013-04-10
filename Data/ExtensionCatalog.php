<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data;

use Silex\Application;

class ExtensionCatalog
{
    protected $app = null;
    protected static $table_name = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'basic_extension_catalog';
    }

    /**
     * Create the table 'extension_catalog'
     *
     * @throws \Exception
     */
    public function createTable ()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `guid` VARCHAR(64) NOT NULL DEFAULT '',
      `name` VARCHAR(64) NOT NULL DEFAULT '',
      `category` VARCHAR(64) NOT NULL DEFAULT '',
      `group` VARCHAR(64) NOT NULL DEFAULT '',
      `release` VARCHAR(16) NOT NULL DEFAULT '',
      `date` DATE NOT NULL DEFAULT '0000-00-00',
      `info` TEXT NOT NULL,
    	`logo_blob` BLOB NOT NULL,
      `logo_type` ENUM ('jpg','png') NOT NULL DEFAULT 'jpg',
      `logo_width` INT NOT NULL DEFAULT '0',
      `logo_height` INT NOT NULL DEFAULT '0',
      `logo_size` INT NOT NULL DEFAULT '0',
      `timestamp` TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE (`guid`)
    )
    COMMENT='The extension catalog table for the kitFramework'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addDebug("Created table '".self::$table_name."' for the class ExtensionCatalog");
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
    } // createTable()

    public function selectIDbyGUID($guid)
    {
        try {
            $SQL = "SELECT `id` FROM `".self::$table_name."` WHERE `guid`='$guid'";
            $result = $this->app['db']->fetchAssoc($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
        return (is_array($result) && isset($result['id'])) ? (int) $result['id'] : null;
    }

    public function select($id)
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` WHERE `id`='$id'";
            $result = $this->app['db']->fetchAssoc($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
        return (is_array($result) && isset($result['id'])) ? $result : array();
    }

    public function insert($data)
    {
        try {
            $insert = array();
            foreach ($data as $key => $value) {
                // quote the keys!
                $insert[$this->app['db']->quoteIdentifier($key)] = (is_string($value)) ? $this->app['utils']->sanitizeVariable($value) : $value;
            }
            $this->app['db']->insert(self::$table_name, $insert);
            return $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Update the record for given ID with the $data
     *
     * @param integer $id
     * @param array $data
     * @throws \Exception
     */
    public function update($id, $data)
    {
        try {
            $update = array();
            foreach ($data as $key => $value)
                // quote the keys!
                $update[$this->app['db']->quoteIdentifier($key)] = (is_string($value)) ? $this->app['utils']->sanitizeVariable($value) : $value;
            $this->app['db']->update(self::$table_name, $update, array('id' => $id));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function selectAll($order_by='name')
    {
        try {
            $SQL = "SELECT * FROM `".self::$table_name."` ORDER BY `$order_by` ASC";
            $result = $this->app['db']->fetchAll($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage());
        }
        return $result;
    }
}
