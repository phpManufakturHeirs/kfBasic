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

use Silex\Application;

class Help {

    const USERAGENT = 'kitFramework:Basic';

    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function getContent($info_path)
    {
        $info = $this->app['utils']->readConfiguration($info_path);
        if (isset($info['help'][$this->app['locale']]['gist_id'])) {
            $gist_id = $info['help'][$this->app['locale']]['gist_id'];
            $gist_link = (isset($info['help'][$this->app['locale']]['link'])) ? $info['help'][$this->app['locale']]['link'] : '';
        }
        elseif (isset($info['help']['en']['gist_id'])) {
            $gist_id = $info['help']['en']['gist_id'];
            $gist_link = (isset($info['help']['en']['link'])) ? $info['help']['en']['link'] : '';
        }
        else {
            return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'kitcommand.help.unavailable.twig'),
                array('command' => $info['command']));
        }
        $ch = curl_init("https://api.github.com/gists/$gist_id");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USERAGENT);
        $result = curl_exec($ch);
        if (!curl_errno($ch)) {
            $curl_info = curl_getinfo($ch);
        }
        curl_close($ch);
        if (isset($curl_info) && isset($curl_info['http_code']) && ($curl_info['http_code'] == '200')) {
            $result = json_decode($result, true);
            if (isset($result['files'])) {
                foreach ($result['files'] as $file) {
                    if (isset($file['content'])) {
                        $help = array(
                            'command' => $info['command'],
                            'content' => $file['content'],
                            'link' => $gist_link
                        );
                        return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'kitcommand.help.twig'),
                            array('help' => $help));
                    }
                }
            }
        }
        else {
            return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'kitcommand.help.unavailable.twig'),
                array('command' => $info['command']));
        }
    }
}