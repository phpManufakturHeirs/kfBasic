<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 */

namespace phpManufaktur\Updater;

use Silex\Application;
use phpManufaktur\Basic\Control\gitHub\gitHub;
use phpManufaktur\Basic\Control\cURL\cURL;
use phpManufaktur\Basic\Control\unZip\unZip;
use phpManufaktur\Basic\Control\Welcome;

/**
 * Updater Class for the kitFramework
 *
 * IMPORTANT
 * This class will never executed within phpManufaktur/Basic/Control/Updater,
 * it will ever placed (copied) at phpManufaktur/Updater to prevent conflicts
 * while updating phpManufaktur/Basic itself.
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
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
    public function getMessage ()
    {
        return self::$message;
    }

    public function setMessage($message, $params=array())
    {
        self::$message .= $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'framework/message.twig'),
        array(
            'message' => $this->app['translator']->trans($message, $params)
        ));
    }

    /**
     * Check if a message is active
     *
     * @return boolean
     */
    public function isMessage()
    {
        return !empty(self::$message);
    }

    /**
     * Clear the existing message(s)
     */
    public function clearMessage()
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
        $github = new gitHub($this->app);
        $release = null;
        if (false === ($tag_url = $github->getLastRepositoryZipUrl($organization, $repository, $release))) {
            throw new \Exception($this->app['translator']->trans("Can't read the the %repository% from %organization% at Github!",
                array('%repository%' => $repository, '%organization%' => $organization)));
        }

        $cURL = new cURL($this->app);

        $target_path = FRAMEWORK_TEMP_PATH.'/repository.zip';
        $cURL->DownloadRedirectedURL($tag_url, $target_path);

        // repository.zip is in temp directory
        if (!file_exists($target_path)) {
            throw new \Exception($this->app['translator']->trans("Can't open the file <b>%file%</b>!",
                array('%file%' => substr($target_path, strlen(FRAMEWORK_PATH)))));
        }

        // init unZip
        $unZip = new unZip();
        $unZip->setUnZipPath(FRAMEWORK_TEMP_PATH.'/repository');
        $unZip->checkDirectory($unZip->getUnZipPath());
        $unZip->extract($target_path);
        $files = $unZip->getFileList();
        if (null === ($subdirectory = $this->getFirstSubdirectory($unZip->getUnZipPath()))) {
            throw new \Exception($this->app['translator']->trans('The received repository has an unexpected directory structure!'));
        }
        $source_directory = $unZip->getUnZipPath().'/'.$subdirectory;
        $extension = $this->app['utils']->readConfiguration($source_directory.'/extension.json');
        if (!isset($extension['path'])) {
            throw new \Exception($this->app['translator']->trans('The received extension.json does not specifiy the path of the extension!'));
        }
        $target_directory = FRAMEWORK_PATH.$extension['path'];

        if (!file_exists($target_directory)) {
            if (!mkdir($target_directory, 0755, true)) {
                throw new \Exception($this->app['translator']->trans('Can\'t create the target directory for the extension!'));
            }
        }

        if (!$this->app['utils']->xcopy($source_directory, $target_directory)) {
            throw new \Exception($this->app['translator']->trans('Could not move the unzipped files to the target directory.'));
        }

        $mode = $this->app['request']->query->get('mode', 'install');

        if (($mode == 'upgrade') && file_exists($target_directory.'/extension.upgrade.php')) {
            $result = include_once $target_directory.'/extension.upgrade.php';
            if (is_string($result) && !empty($result)) {
                $this->setMessage($result);
            }
        }
        elseif (file_exists($target_directory.'/extension.install.php')) {
            $result = include_once $target_directory.'/extension.install.php';
            if (is_string($result) && !empty($result)) {
                $this->setMessage($result);
            }
        }

        $this->setMessage('Success! The extension %extension% is installed.', array('%extension%' => $extension['name']));

        // return to the welcome dialog
        $Welcome = new Welcome($this->app);
        $Welcome->setMessage($this->getMessage());
        return $Welcome->exec();
    }

}

