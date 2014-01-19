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

class Account extends Alert
{
    protected static $usage = null;

    /**
     * Initialize the class
     *
     * @param Application $app
     */
    protected function initialize(Application $app) {
        parent::initialize($app);
        self::$usage = $app['request']->get('usage', 'framework');
    }

    /**
     * Return the Account dialog
     */
    public function exec(Application $app)
    {
        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/account.twig'),
            array(
                'usage' => self::$usage,
                'alert' => $this->getAlert()
            ));
    }

}
