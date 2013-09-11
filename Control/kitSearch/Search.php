<?php

/**
 * kitFramework::kfBasic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\kitSearch;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Search
{
    public function exec(Application $app, $command)
    {
        try {
            if (!isset($_POST['cms_parameter'])) {
                throw new \Exception('Invalid kitCommand execution: missing the POST CMS parameter!');
            }
            $cms_parameter = $_POST['cms_parameter'];
            $subRequest = Request::create('/search/command/'.$command, 'POST', $cms_parameter);
            // important: we dont want that app->handle() catch errors, so set the third parameter to false!
            $result = $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST , false);
        } catch (\Exception $e) {
            // no search for this kitCommand found or error while executing
            $result = array(
                'search' => array(
                    'text' => $e->getMessage()
                )
            );
            $result = base64_encode(json_encode($result));
        }
        return $result;
    }
}
