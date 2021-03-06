<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\Account;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\DBAL\Connection;
use phpManufaktur\Basic\Data;
use Silex\Application;

/**
 * Class UserProvider implements UserProviderInterface
 * Usermanagement and -roles
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 */
class UserProvider implements UserProviderInterface
{

    protected $app = null;

    /**
     * Constructor for the class UserProvider
     *
     * @param Connection $db
     */
    public function __construct (Application $app)
    {
        $this->app = $app;
    }

    /**
     * Check if the $username exists in the table 'users' and return User object
     * on success.
     *
     * @see \Symfony\Component\Security\Core\User\UserProviderInterface::loadUserByUsername()
     */
    public function loadUserByUsername ($username)
    {
        $frameworkUser = new Data\Security\Users($this->app);
        if (false === ($user = $frameworkUser->selectUser($username))) {
            // user not found - check if the user exists as CMS user!
            $cmsUser = new Data\CMS\Users($this->app);
            $isAdmin = false;
            if (false === ($user = $cmsUser->selectUser($username, $isAdmin))) {
                // give up - user not found
                throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
            }
            // user exists - tell the encoder via "password" to create a new record for this user!
            $role = $isAdmin ? array(
                'ROLE_ADMIN'
            ) : array(
                'ROLE_USER'
            );
            return new User($user['username'], $username, $role, true, true, true, true);
        }
        return new User($user['username'], $user['password'], explode(',', $user['roles']), true, true, true, true);
    } // loadUserByUsername()

    /**
     * Refreshes the user for the account interface.
     *
     * @see \Symfony\Component\Security\Core\User\UserProviderInterface::refreshUser()
     */
    public function refreshUser (UserInterface $user)
    {
        if (! $user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }
        return $this->loadUserByUsername($user->getUsername());
    } // refreshUser()

    /**
     * Whether this provider supports the given user class
     *
     * @see \Symfony\Component\Security\Core\User\UserProviderInterface::supportsClass()
     */
    public function supportsClass ($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    } // supportsClass()

} // class UserProvider
