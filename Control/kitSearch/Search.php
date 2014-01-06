<?php

/**
 * kitFramework::kfBasic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
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
            $subRequest = Request::create('/search/command/'.$command, 'POST', array(
                'search' => $app['request']->get('search'),
                'cms' => $app['request']->get('cms'),
                'parameter' => $app['request']->get('parameter')
            ));
            // important: we dont want that app->handle() catch errors, so set the third parameter to false!
            return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
        } catch (\Exception $e) {
            // no search for this kitCommand found or error while executing
            $result = array(
                'search' => array(
                    'success' => false,
                    'text' => $e->getMessage()
                )
            );
            return $app->json($result);
        }
    }
}
