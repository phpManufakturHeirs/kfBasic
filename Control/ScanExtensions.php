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

class ScanExtensions
{
    public function exec(Application $app)
    {
        $register = new ExtensionRegister($app);
        $register->scanDirectories(ExtensionRegister::GROUP_PHPMANUFAKTUR);
        $register->scanDirectories(ExtensionRegister::GROUP_THIRDPARTY);
        $Welcome = new Welcome($app);
        if ($register->isMessage()) {
            $Welcome->setMessage($register->getMessage());
        }
        else {
            $Welcome->setMessage($app['translator']->trans('<p>Successfull scanned the kitFramework for installed extensions.</p>'));
        }
        return $Welcome->exec($app);
    }
}
