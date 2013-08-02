<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\CMS;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class EmbeddedAdministration
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Called by the iframe embedded in the CMS. The encoded CMS information
     * will be read, provided as session (CMS_TYPE, CMS_VERSION, CMS_LOCALE and
     * CMS_USERNAME), detect the usage (framework or specified CMS) and execute
     * the specified route. The route get as parameter the usage.
     *
     * @todo autologin does not check the user
     * @todo improve the checking and setting of roles
     *
     * @param string $route_to
     * @param string $encoded_cms_information
     * @return Request
     * @link https://github.com/phpManufaktur/kitFramework/wiki/Extensions-%23-Embedded-Administration Embedded Administration
     */
    public function route($route_to, $encoded_cms_information)
    {
        if (false === ($decoded_information = base64_decode($encoded_cms_information))) {
            throw new \Exception("Can't decode the CMS Base64 information parameter!");
        }
        if (false === ($cms = json_decode($decoded_information, true))) {
            throw new \Exception("JSON decoding error!");
        }

        if (!isset($cms['locale']) || !isset($cms['username'])) {
            throw new \Exception("CMS information is incomplete, at minimum needed are locale and username!");
        }

        // save them partial into session
        $this->app['session']->set('CMS_LOCALE', $cms['locale']);
        $this->app['session']->set('CMS_USERNAME', $cms['username']);

        // auto login into the admin area and then execute the extension route
        $secureAreaName = 'admin';
        $user = new User($cms['username'],'', array('ROLE_USER'), true, true, true, true);
        $token = new UsernamePasswordToken($user, null, $secureAreaName, $user->getRoles());
        $this->app['security']->setToken($token);
        $this->app['session']->set('_security_'.$secureAreaName, serialize($token) );

        $usage = ($cms['target'] == 'cms') ? CMS_TYPE : 'framework';

        // sub request to the starting point of Event
        $subRequest = Request::create($route_to, 'GET', array('usage' => $usage));
        return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

}
