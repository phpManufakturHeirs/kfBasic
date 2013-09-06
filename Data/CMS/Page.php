<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Data\CMS;

use Silex\Application;
use phpManufaktur\Basic\Data\CMS\WebsiteBaker\Page as WebsiteBakerPage;
use phpManufaktur\Basic\Data\CMS\LEPTON\Page as LeptonPage;
use phpManufaktur\Basic\Data\CMS\BlackCat\Page as BlackCatPage;

class Page {

    protected $app = null;
    protected $cms = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        switch (CMS_TYPE) {
            case 'WebsiteBaker':
                $this->cms = new WebsiteBakerPage($app); break;
            case 'LEPTON':
                $this->cms = new LeptonPage($app); break;
            case 'BlackCat':
                $this->cms = new BlackCatPage($app); break;
            default:
                throw new \Exception(sprintf("The CMS TYPE <b>%s</b> is not supported!", CMS_TYPE));
        }
    }

    public function getURL($id, $command_parameter=null)
    {
        $parameter = null;
        if (is_array($command_parameter) && (isset($command_parameter['cms']['special']['topic_id']) ||
            isset($command_parameter['cms']['special']['post_id']))) {
            $parameter = array();
            if (isset($command_parameter['cms']['special']['topic_id'])) {
                $parameter['topic_id'] = $command_parameter['cms']['special']['topic_id'];
            }
            if (isset($command_parameter['cms']['special']['post_id'])) {
                $parameter['topic_id'] = $command_parameter['cms']['special']['post_id'];
            }
        }
        return $this->cms->getURL($id, $parameter);
    }

}
