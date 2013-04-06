<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */
namespace phpManufaktur\Basic\Control;

use Silex\Application;

class Account
{

    protected $app;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct (Application $app)
    {
        $this->app = $app;
    }

    /**
     * Return the Account dialog
     */
    public function showDialog ()
    {
        return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'account.twig'), array());
    }

} // class Account