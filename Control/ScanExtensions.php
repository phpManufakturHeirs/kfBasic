<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control;

use Silex\Application;
use phpManufaktur\Basic\Control\Pattern\Alert;

class ScanExtensions extends Alert
{
    public function exec(Application $app)
    {
        $register = new ExtensionRegister($app);
        $register->scanDirectories(ExtensionRegister::GROUP_PHPMANUFAKTUR);
        $register->scanDirectories(ExtensionRegister::GROUP_THIRDPARTY);
        $Welcome = new Welcome($app);
        if (!$register->isAlert()) {
            $Welcome->setAlert('Successfull scanned the kitFramework for installed extensions.',
                array(), self::ALERT_TYPE_SUCCESS);
        }
        return $Welcome->controllerFramework($app);
    }
}
