<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\Account;

use Silex\Application;
use Symfony\Component\Security\Core\User\User as SymfonyUser;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use phpManufaktur\Basic\Data\Security\Users as FrameworkUser;
use phpManufaktur\Basic\Data\CMS\Users as CMSuser;

class Account
{
    protected $app = null;
    protected $FrameworkUser = null;
    protected $CMSuser = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->FrameworkUser = new FrameworkUser($app);
        $this->CMSuser = new CMSuser($app);
    }

    /**
     * Check if the user is authenticated
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        $token = $this->app['security']->getToken();
        if (is_null($token)) {
            return false;
        }
        $user = $token->getUser();
        return ($user == 'anon.') ? false : true;
    }

    /**
     * Return the display name of the authenticated user or ANONYMOUS otherwise
     *
     * @return string|unknown
     */
    public function getDisplayName()
    {
        $token = $this->app['security']->getToken();
        if (is_null($token))
            return 'ANONYMOUS';

        $user = $token->getUser();

        if ($user == 'anon.') {
            return 'ANONYMOUS';
        }
        // get the user record
        if (false === ($user_data = $this->FrameworkUser->selectUser($user->getUsername()))) {
            // user not found!
            return 'ANONYMOUS';
        }
        $display_name = (isset($user_data['displayname']) && ! empty($user_data['displayname'])) ? $user_data['displayname'] : $user_data['username'];
        return $display_name;
    }

    /**
     * Return the USER name of the authenticated user or ANONYMOUS otherwise
     *
     * @return string
     */
    public function getUserName()
    {
        $token = $this->app['security']->getToken();
        if (is_null($token))
            return 'ANONYMOUS';

        $user = $token->getUser();

        return ($user == 'anon.') ? 'ANONYMOUS' : $user->getUsername();
    }

    /**
     * Return the user data record of the given user or false if the user does
     * not exists
     *
     * @param string $username
     * @return boolean|Ambigous <boolean, multitype:unknown >
     */
    public function getUserData($username)
    {
        return $this->FrameworkUser->selectUser($username);
    }

    /**
     * Check if the given CMS user has administrator privileges at the CMS
     *
     * @param string $username
     * @return boolean
     */
    public function checkUserIsCMSAdministrator($username)
    {
        $is_admin = false;
        return (!$this->CMSuser->selectUser($username, $is_admin) || !$is_admin) ? false : true;
    }

    /**
     * Check if the user has as account at the kitFramework
     *
     * @param string $username
     */
    public function checkUserHasFrameworkAccount($username)
    {
        return $this->FrameworkUser->existsUser($username);
    }

    /**
     * Login the user with the given roles into a secured area
     *
     * @param string $username
     * @param array $roles
     * @param string $area_name
     */
    public function loginUserToSecureArea($username, $roles, $secure_area_name='general')
    {
        $user = new SymfonyUser($username,'', $roles, true, true, true, true);
        $token = new UsernamePasswordToken($user, null, $secure_area_name, $user->getRoles());
        $this->app['security']->setToken($token);
        $this->app['session']->set('_security_'.$secure_area_name, serialize($token) );
    }

    /**
     * Get the CMS account of the given user
     *
     * @param string $username
     * @return array|boolean
     */
    public function getUserCMSAccount($username)
    {
        return $this->CMSuser->selectUser($username);
    }

    /**
     * Create a kitFramework account
     *
     * @param string $username
     * @param string $email
     * @param string $password
     * @param array|string $roles
     * @param string $displayname
     */
    public function createAccount($username, $email, $password, $roles, $displayname='')
    {
        $data = array(
            'username' => $username,
            'email' => $email,
            'password' => $this->FrameworkUser->encodePassword($password),
            'displayname' => ($displayname != '') ? $displayname : $username,
            'roles' => $roles
        );
        $this->FrameworkUser->insertUser($data);
    }

}
