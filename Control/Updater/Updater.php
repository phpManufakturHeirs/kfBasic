<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 *
 * IMPORTANT
 * This class will never executed within phpManufaktur/Basic/Control/Updater,
 * it will ever placed (copied) at phpManufaktur/Updater to prevent conflicts
 * while updating phpManufaktur/Basic itself.
 */

namespace phpManufaktur\Updater;

use Silex\Application;
use phpManufaktur\Basic\Control\gitHub\gitHub;
use phpManufaktur\Basic\Control\cURL\cURL;
use phpManufaktur\Basic\Control\unZip\unZip;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class Updater
{
    protected $app = null;
    protected static $message;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @return the $message
     */
    public static function getMessage ()
    {
        return self::$message;
    }

    /**
     * @param string $message
     */
    public static function setMessage ($message)
    {
        self::$message .= $message;
    }

    /**
     * Check if a message is active
     *
     * @return boolean
     */
    public static function isMessage()
    {
        return !empty(self::$message);
    }

    /**
     * Clear the existing message(s)
     */
    public static function clearMessage()
    {
        self::$message = '';
    }

    /**
     * Search for the first subdirectory below the given path
     *
     * @param string $path
     * @return string|NULL subdirectory, null if search fail
     */
    protected function getFirstSubdirectory($path)
    {
        $handle = opendir($path);
        // we loop through the directory to get the first subdirectory ...
        while (false !== ($directory = readdir($handle))) {
            if ('.' == $directory || '..' == $directory)
                continue;
            if (is_dir($path .'/'. $directory)) {
                // ... here we got it!
                return $directory;
            }
        }
        return null;
    }

    public function getLastGithubRepository($organization, $repository)
    {
        // init GitHub
        $github = new gitHub();
        $release = null;
        if (false === ($tag_url = $github->getLastRepositoryZipUrl($organization, $repository, $release))) {
            throw new \Exception($this->app['translator']->trans("<p>Can't read the the %repository% from %organization% at Github!</p>",
                array('%repository%' => $repository, '%organization%' => $organization)));
        }

        $cURL = new cURL($this->app);
        $info = array();
        $target_path = FRAMEWORK_TEMP_PATH.'/repository.zip';
        $cURL->DownloadRedirectedURL($tag_url, $target_path);

        // repository.zip is in temp directory
        if (!file_exists($target_path)) {
            throw new \Exception($this->app['translator']->trans("<p>Can't open the file <b>%file%</b>!</p>",
                array('%file%' => substr($target_path, strlen(FRAMEWORK_PATH)))));
        }
        // init unZip
        $unZip = new unZip();
        $unZip->setUnZipPath(FRAMEWORK_TEMP_PATH.'/repository');
        $unZip->checkDirectory($unZip->getUnZipPath());
        $unZip->extract($target_path);
        $files = $unZip->getFileList();
        if (null === ($subdirectory = $this->getFirstSubdirectory($unZip->getUnZipPath()))) {
            throw new \Exception($this->app['translator']->trans('<p>The received repository has an unexpected directory structure!</p>'));
        }
        $this->setMessage('Success!');

        // sub request to the welcome dialog
        $cms = $this->app['request']->get('usage');
        $usage = is_null($cms) ? 'framework' : $cms;
        $subRequest = Request::create('/admin/welcome', 'GET', array('usage' => $usage));
        return $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    public function exec($github_organization, $github_repository)
    {

    }
}

