<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control;

use Silex\Application;

class ScanCatalog
{
    public function exec(Application $app)
    {
        $catalog = new ExtensionCatalog($app);
        $catalog->getOnlineCatalog();
        $Welcome = new Welcome($app);
        $Welcome->setMessage($app['translator']->trans('<p>Successfull scanned the kitFramework online catalog for available extensions.</p>'));
        return $Welcome->exec($app);
    }
}
