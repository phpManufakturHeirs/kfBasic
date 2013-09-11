<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 */

namespace phpManufaktur\Basic\Control\kitFilter;

use Silex\Application;

class Filter
{
    public function exec(Application $app, $filter)
    {
        if (!isset($_POST['cms_parameter'])) {
            throw new \Exception('Invalid kitFilter execution: missing the POST CONTENT parameter!');
        }
        $cms_parameter = $_POST['cms_parameter'];
        $content = isset($cms_parameter['content']) ? $cms_parameter['content'] : '- no content retrieved! -';

        return 'FILTER INACTIVE!'.$content;
    }
}
