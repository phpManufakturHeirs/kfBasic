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
use phpManufaktur\Basic\Data\Setting;
use phpManufaktur\Basic\Data\ExtensionRegister;
use phpManufaktur\Basic\Data\kitCommandParameter;

/**
 * Setup all needed database tables and initialize the kitFramework
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class Setup
{

    /**
     * Create the database tables for the BASIC extension of the kitFramework
     *
     * @param Application $app
     */
    public function exec(Application $app)
    {
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

        return $app['translator']->trans('Successfull installed the extension %extension%.',
            array('%extension%' => 'Basic'));
    }

}
