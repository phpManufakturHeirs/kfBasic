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

class ListCommands extends kitCommand
{

    public function getList()
    {
        // get the CMS locale
        $locale = $this->getCMSlocale();
        // get all routing objects
        $kitCommands = array();
        // get an iterator through all kitFramework routes
        $patterns = $this->app['routes']->getIterator()->current()->all();
        // walk through the routing objects
        foreach ($patterns as $pattern) {
            $match = $pattern->getPattern();
            // we are searching for all matching kitCommands, starting with '/command/'
            if ((strpos($match, '/command/') !== false) && (strpos($match, '/command/') == 0))  {
                $command = substr($match, strlen('/command/'));
                if (!isset($command[0]) || ($command[0] == '{')) {
                    // add no subroutings to the list!
                    continue;
                }
                $info = array();
                if ((null !== ($info_path = $pattern->getOption('info'))) && file_exists($info_path)) {
                    // info file exists so we use the additional informations
                    $config = $this->app['utils']->readConfiguration($info_path);
                    // help available?
                    $gist_id = (isset($config['help'][$locale]['link'])) ? true :
                        ((isset($config['help']['en']['link'])) ? true : false);
                    // return array
                    $info = array(
                        'vendor' => array(
                            'name' => (isset($config['vendor']['name'])) ? $config['vendor']['name'] : null,
                            'url' => (isset($config['vendor']['url'])) ? $config['vendor']['url'] : null
                        ),
                        'info' => array(
                            'name' => $command,
                            'url' => (isset($config['info'][$locale]['link'])) ? $config['info'][$locale]['link'] :
                                        ((isset($config['info']['en']['link'])) ? $config['info']['en']['link'] : null)
                        ),
                        'wiki' => array(
                            'url' => (isset($config['wiki'][$locale]['link'])) ? $config['wiki'][$locale]['link'] :
                                        ((isset($config['wiki']['en']['link'])) ? $config['wiki']['en']['link'] : null)
                        ),
                        'issues' => array(
                            'url' => (isset($config['issues'][$locale]['link'])) ? $config['issues'][$locale]['link'] :
                                        ((isset($config['issues']['en']['link'])) ? $config['issues']['en']['link'] : null)
                        ),
                        'support' => array(
                            'url' => (isset($config['support'][$locale]['link'])) ? $config['support'][$locale]['link'] :
                                        ((isset($config['support']['en']['link'])) ? $config['support']['en']['link'] : null)
                        ),
                        'help' => array(
                            'url' => $gist_id ? FRAMEWORK_URL."/basic/help/$command?pid=".$this->getParameterID() : ''
                            ),
                        'name' => (isset($config['name'][$locale])) ? $config['name'][$locale] :
                                    ((isset($config['name']['en'])) ? $config['name']['en'] : null),
                        'description' => (isset($config['description'][$locale])) ? $config['description'][$locale] :
                                            ((isset($config['description']['en'])) ? $config['description']['en'] : null)
                    );
                }
                $kitCommands[$command] = array(
                    'command' => $command,
                    'route' => $match,
                    'info' => $info,
                    'search' => false
                );
            }
            elseif ((strpos($match, '/search/command/') !== false) && (strpos($match, '/search/command/') == 0)) {
                // this kitCommand support the CMS search function!
                $command = substr($match, strlen('/search/command/'));
                $command = substr($command, 0, strpos($command, '/'));
                $kitCommands[$command]['search'] = true;
            }
        }
        $kCommands = array();
        foreach ($kitCommands as $command) {
            // to prevent "search" widows ...
            if (isset($command['search']) && !isset($command['command'])) continue;
            $kCommands[$command['command']] = $command;
        }
        // sort the kitCommands
        ksort($kCommands);
        // return the kitCommands list
        return $this->app['twig']->render($this->app['utils']->templateFile('@phpManufaktur/Basic/Template', 'kitcommand.list.twig'),
            array(
                'commands' => $kCommands,
                'basic' => $this->getBasicSettings()
        ));

    }
}