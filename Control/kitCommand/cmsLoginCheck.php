<?php

/**
 * kitFramework::kfBasic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\kitCommand;

use phpManufaktur\Basic\Control\kitCommand\Basic as kitCommand;
use phpManufaktur\Basic\Data\CMS\Users;

require_once MANUFAKTUR_PATH.'/Basic/Control/CMS/Login.php';

class cmsLoginCheck extends kitCommand
{

    public function check()
    {
        $user_name = $this->app['request']->get('user_name', '');
        $user_pass = $this->app['request']->get('user_pass', '');
        $Users = new Users($this->app);
        if (false === ($user = $Users->selectUser($user_name))) {
            // unknown user
            $Login = new cmsLogin($this->app);
            $Login->setMessage('Unknown user');
            return $Login->getLoginDialog();
        }
        if (!isset($user['password']) || ($user['password'] != md5($user_pass))) {
            // login failed
            $Login = new cmsLogin($this->app);
            $Login->setMessage('Please check the username and password and try again!');
            return $Login->getLoginDialog();
        }
        try {
            cmsLoginUser($user);
        } catch (\Exception $e) {
            throw new $e->getMessage();
        }
        return 'ok';

    }
}