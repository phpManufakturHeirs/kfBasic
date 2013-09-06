<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
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
    protected $app = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function exec()
    {
        // create the framework user table
        $users = new Users($this->app);
        $users->createTable();

        // create the Extension Catalog
        $catalog = new ExtensionCatalog($this->app);
        $catalog->createTable();

        // create the setting table
        $setting = new Setting($this->app);
        $setting->createTable();
        $setting->insertDefaultValues();

        // create the table for the extension register
        $register = new ExtensionRegister($this->app);
        $register->createTable();

        // create the table for the kitCommand parameters
        $cmdParameter = new kitCommandParameter($this->app);
        $cmdParameter->createTable();

    }

}
