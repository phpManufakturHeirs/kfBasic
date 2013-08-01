<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\CMS\WebsiteBaker;

use Silex\Application;
use phpManufaktur\Basic\Data\CMS\Addons;

class InstallAdminTool
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function exec($extension_path, $extension_route)
    {
        $extension = $this->app['utils']->readJSON($extension_path);

        if (!isset($extension['name'])) {
            throw new \Exception('The extension.json does not contain the extension name!');
        }
        if (!isset($extension['description']['en']['short'])) {
            throw new \Exception('Missing the short description for the extension in english language!');
        }
        if (!isset($extension['release']['number'])) {
            throw new \Exception('Missing the release number of the extension!');
        }
        if (!isset($extension['vendor'])) {
            throw new \Exception('Missing the vendor name of the extension!');
        }
        if (!isset($extension['license'])) {
            throw new \Exception('Missing the license type for the extension!');
        }

        try {
            // begin transaction
            $this->app['db']->beginTransaction();

            $data = array(
                'name' => $extension['name'],
                'directory' => 'kit_framework_'.strtolower(trim($extension['name'])),
                'guid' => isset($extension['guid']) ? $extension['guid'] : '',
                'description' => $extension['description']['en']['short'],
                'version' => $extension['release']['number'],
                'author' => $extension['vendor'],
                'license' => $extension['license'],
                'platform' => (CMS_TYPE == 'WebsiteBaker') ? '2.8.x' : '1.x',
                'type' => 'module',
                'function' => 'tool'
            );

            if (CMS_TYPE == 'WebsiteBaker') {
                unset($data['guid']);
            }
            $addon = new Addons($this->app);

            if ($addon->existsDirectory($data['directory'])) {
                // update the existing record
                $addon->update($data['directory'], $data);
            }
            else {
                // insert a new record
                $addon->insert($data);
            }

            $data['EXTENSION_ROUTE'] = $extension_route;

            $this->app['filesystem']->mkdir(CMS_PATH.'/modules/'.$data['directory']);

            $search = array();
            $replace = array();
            foreach ($data as $key => $value) {
                $search[] = sprintf('{%s}', strtoupper($key));
                $replace[] = $value;
            }

            // generate files
            $files = array('index', 'info', 'tool', 'install', 'uninstall');

            foreach ($files as $file) {
                // loop through the files, replace content and write them to the desired /modules directory
                if (file_exists(MANUFAKTUR_PATH."/Basic/Template/default/cms/setup/websitebaker/{$file}.htt")) {
                    $content = file_get_contents(MANUFAKTUR_PATH."/Basic/Template/default/cms/setup/websitebaker/{$file}.htt");
                    file_put_contents(CMS_PATH.'/modules/'.$data['directory'].'/'.$file.'.php', str_ireplace($search, $replace, $content));
                }
            }

            if (file_exists(MANUFAKTUR_PATH."/Basic/Template/default/cms/setup/websitebaker/language.htt")) {
                $content = file_get_contents(MANUFAKTUR_PATH."/Basic/Template/default/cms/setup/websitebaker/language.htt");
                // create a language directory
                $this->app['filesystem']->mkdir(CMS_PATH.'/modules/'.$data['directory'].'/languages');
                // loop through the languages available in the extension.json and create module descriptions for the CMS
                foreach ($extension['description'] as $lang => $lang_array) {
                    $lang_search = array('{NAME}', '{AUTHOR}', '{DESCRIPTION}');
                    $lang_replace = array($data['name'], $data['author'], $lang_array['short']);
                    file_put_contents(CMS_PATH.'/modules/'.$data['directory'].'/languages/'.strtoupper($lang).'.php',
                        str_ireplace($lang_search, $lang_replace, $content));
                }
            }

            // commit the transaction
            $this->app['db']->commit();
        }
        catch (\Exception $e) {
            // roolback the transaction
            $this->app['db']->rollback();
            throw new \Exception($e);
        }
    }
}
