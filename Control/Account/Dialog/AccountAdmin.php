<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\Account\Dialog;

use Silex\Application;
use phpManufaktur\Basic\Control\Pattern\Alert;

class AccountAdmin extends Alert
{

    public function ControllerAccountList(Application $app)
    {
        return __METHOD__;
    }
}
