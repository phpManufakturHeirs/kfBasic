<?php

/**
 * kitConnect
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/kitEvent
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */
namespace phpManufaktur\Basic\Control\gitHub;

use Silex\Application;
/**
 * Class to access GitHub
 *
 * @author Ralf Hertsch <ralf.hertsch@phpManufaktur.de>
 *
 */
class gitHub
{

    const USERAGENT = 'kitFramework_Catalog';

    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * GET command to GitHub
     *
     * @param string $command API get command
     * @param array &$result reference to the result array
     * @param array &$info reference to the info array
     * @return boolean
     */
    protected function get ($command, &$result = array(), &$info = array())
    {
        if (strpos($command, 'https://api.github.com') !== 0)
            $command = "https://api.github.com$command";
        if (false === ($ch = curl_init($command))) {
            throw new \Exception('Got no handle for cURL!');
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USERAGENT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_USERPWD, "df89bd3276fe41916c444e39b86b786a938e9a42:x-oauth-basic");

        // set proxy if needed
        $this->app['utils']->setCURLproxy($ch);

        if (false === ($result = curl_exec($ch))) {
            throw new \Exception(curl_error($ch));
        }
        if (! curl_errno($ch)) {
            $info = curl_getinfo($ch);
            $this->app['monolog']->addDebug(print_r($info,1), array(__METHOD__, __LINE__));
            // check rate limit
            $check = curl_init('https://api.github.com/rate_limit');
            curl_setopt($check, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($check, CURLOPT_USERAGENT, self::USERAGENT);
            curl_setopt($check, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($check, CURLOPT_SSL_VERIFYPEER, false);
            // set proxy if needed
            $this->app['utils']->setCURLproxy($check);
            $check_result = curl_exec($check);
            curl_close($check);
            if($check_result)
            {
            $check_result = json_decode($check_result, true);
            if($check_result['resources']['core']['remaining'] == 0)
            {
                $result = $check_result;
                $result['message'] = sprintf(
                    'GitHub API rate limit (%s) exceeded. Please wait until %s and try again.',
                    $check_result['resources']['core']['limit'],
                    strftime('%c', $check_result['resources']['core']['reset'])
                );
            return false;
        }
            }

        }
        curl_close($ch);
        $result = json_decode($result, true);
        return (! isset($info['http_code']) || ($info['http_code'] != '200')) ? false : true;
    } // gitGet()

    /**
     * Get the tags for the $repository and return the last one in $last_tag.
     * This function uses version_compare() to get the last repository
     *
     * @param string $organization
     * @param string $repository
     * @param array &$last_tag reference
     * @throws \Exception
     * @return boolean
     */
    public function getTags ($organization, $repository, &$last_tag)
    {
        // API command to get a list of the repository tags
        $command = "/repos/$organization/$repository/tags";

        $result = array();
        $info = array();

        if ($this->get($command, $result, $info)===false) {
            if (isset($info['http_code']) && isset($result['message']))
                $error_message = sprintf('[GitHub Error] HTTP Code: %s - %s', $info['http_code'], $result['message']);
            elseif (isset($info['http_code']))
                $error_message = sprintf('[GitHub Error] HTTP Code: %s - no further informations.', $info['http_code']);
            else
                $error_message = '[GitHub Error] Unknown connection error, got no result!';
            throw new \Exception($error_message);
        }

        // no result?
        if (count($result) < 1)
            return false;

            // we only want the last release number!
        $last_tag = array();
        foreach ($result as $release) {
            if (! isset($release['name']))
                throw new \Exception('[GitHub Error] Result array has not the expected structure!');
            if (empty($last_tag)) {
                $last_tag = $release;
                continue;
            }
            // use version_compare for comparison
            if (version_compare($last_tag['name'], $release['name']) == - 1)
                $last_tag = $release;
        }
        return true;
    } // getTags()

    /**
     * Get the URL for the ZIP archive of the repository with the highest version in tag
     *
     * @param string $organization
     * @param string $repository
     * @param string &$version reference
     * @return boolean Ambigous
     */
    public function getLastRepositoryZipUrl($organization, $repository, &$version = '')
    {
        $last_tag = array();
        if (! $this->getTags($organization, $repository, $last_tag))
            return false;
            // get the tag name (version)
        $version = $last_tag['name'];
        return $last_tag['zipball_url'];
    } // getLastRepositoryZipUrl()

} // class gitHub
