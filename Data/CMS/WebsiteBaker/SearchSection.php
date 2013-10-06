<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\CMS\WebsiteBaker;

use Silex\Application;

class SearchSection
{
    /**
     * Add a kit_framework_search section if none exists
     *
     * @param Application $app
     * @throws \Exception
     */
    public function addSearchSection(Application $app)
    {
        try {
            $SQL = "SELECT `section_id` FROM `".CMS_TABLE_PREFIX."sections`, `".CMS_TABLE_PREFIX."pages` WHERE `module`='kit_framework_search' AND `visibility`='public'";
            if (($section_id = $app['db']->fetchColumn($SQL)) < 1) {
                // missing the search section for the kitFramework
                $SQL = "SELECT `page_id` FROM `".CMS_TABLE_PREFIX."pages` WHERE `visibility`='public'";
                $page_id = $app['db']->fetchColumn($SQL);
                $SQL = "SELECT `position` FROM `".CMS_TABLE_PREFIX."sections` WHERE `page_id`='$page_id' ORDER BY `position` DESC LIMIT 1";
                $position = $app['db']->fetchColumn($SQL);
                // insert the search section
                $app['db']->insert(CMS_TABLE_PREFIX.'sections', array(
                    'block' => 1,
                    'publ_end' => 0,
                    'publ_start' => 0,
                    'module' => 'kit_framework_search',
                    'page_id' => $page_id,
                    'position' => $position++
                ));
                $app['monolog']->addInfo("Inserted a kit_framework_search section to page ID $page_id", array(__METHOD__, __LINE__));
            }
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
