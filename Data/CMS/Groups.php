<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\CMS;

use Silex\Application;

/**
 * Class to access the CMS user groups
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class Groups
{

    protected $app = null;

    public function __construct (Application $app)
    {
        $this->app = $app;
    } // __construct()

    public function selectGroup($group_id)
    {
        try {
            $SQL = "SELECT * FROM `" . CMS_TABLE_PREFIX . "groups` WHERE `group_id`='$group_id'";
            $result = $this->app['db']->fetchAssoc($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
        return (!isset($result['group_id'])) ? false : $result;
    } // selectUser()

} // class Users