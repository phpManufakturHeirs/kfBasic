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

/**
 * Class to get help informations from the desired GIST and display them within
 * the kitCommand
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 *
 */
class Help extends kitCommand {

    const USERAGENT = 'kitFramework:Basic';

    /**
     * Get the content of the specified help file from Gist
     *
     * @param string $info_path to the command.xxx.json file
     */
    public function getContent($info_path)
    {
        $info = $this->app['utils']->readConfiguration($info_path);
        $locale = $this->app['request']->query->get('locale', $this->getCMSlocale());
        if (isset($info['help'][$locale]['gist_id'])) {
            $gist_id = $info['help'][$locale]['gist_id'];
            $gist_link = (isset($info['help'][$locale]['link'])) ? $info['help'][$locale]['link'] : '';
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
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
                            'link' => $gist_link,
                            'help' => FRAMEWORK_URL.'/command/help?pid='.$this->getParameterID()
                        );
                        return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'kitcommand/help.content.twig'),
                            array('help' => $help));
                    }
                }
            }
        }
        else {
            return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'kitcommand/help.unavailable.twig'),
                array(
                    'command' => $info['command'],
                    'curl_info' => isset($curl_info) ? $curl_info : '- no information available -'
            ));
        }
    }

    /**
     * Get the help page for the specified kitCommand from Github Gist
     *
     * @param string $command
     */
    public function getHelpPage($command)
    {
        if (false === ($info_path = $this->getInfoPath($command))) {
            $this->setMessage('There is no help available for the kitCommand <b>%command%</b>.', array('%command%' => $command));
            $help = '';
        }
        else {
            $help = $this->getContent($info_path);
            $info = $this->app['utils']->readConfiguration($info_path);
        }

        $locale = $this->app['request']->query->get('locale', $this->getCMSlocale());

        return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'kitcommand/help.twig'),
            array(
                'help' => $help,
                //'command' => $command,
                'basic' => $this->getBasicSettings(),
                'command' => array(
                    'command' => $command,
                    'general' => array(
                        'help' => array(
                            'url' => FRAMEWORK_URL.'/basic/help/help?pid='.$this->getParameterID()
                        ),
                        'list' => array(
                            'url' => FRAMEWORK_URL.'/basic/list?pid='.$this->getParameterID()
                        ),
                        'extern' => array(
                            'url' => FRAMEWORK_URL."/basic/help/$command"
                        )
                    ),
                    'info' => array(
                        'url' => (isset($info['info'][$locale]['link'])) ? $info['info'][$locale]['link'] :
                                    ((isset($info['info']['en']['link'])) ? $info['info']['en']['link'] : null)
                    ),
                    'wiki' => array(
                        'url' => (isset($info['wiki'][$locale]['link'])) ? $info['wiki'][$locale]['link'] :
                                    ((isset($info['wiki']['en']['link'])) ? $info['wiki']['en']['link'] : null)
                    ),
                    'issues' => array(
                        'url' => (isset($info['issues'][$locale]['link'])) ? $info['issues'][$locale]['link'] :
                                    ((isset($info['issues']['en']['link'])) ? $info['issues']['en']['link'] : null)
                    ),
                    'support' => array(
                        'url' => (isset($info['support'][$locale]['link'])) ? $info['support'][$locale]['link'] :
                                    ((isset($info['support']['en']['link'])) ? $info['support']['en']['link'] : null)
                    )
                ),
            ));
    }

 }
