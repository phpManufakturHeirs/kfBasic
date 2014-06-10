<?php

namespace phpManufaktur\Basic\Control\kitCommand;

use phpManufaktur\Basic\Control\kitCommand\Basic;
use Silex\Application;

class Simulate extends Basic
{

    /**
     * Prompt the given kitCommand expression
     *
     * @param Application $app
     */
    public function ControllerSimulate(Application $app)
    {
        $this->initParameters($app);

        $this->setFrameAdd(4);

        $parameter = $this->getCommandParameters();
        if (!isset($parameter['expression'])) {
            $this->setAlert('Missing the parameter "expression"!');
        }

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'kitcommand/bootstrap/simulate.twig'),
            array(
                'parameter' => $parameter,
                'basic' => $this->getBasicSettings()
        ));
    }

    /**
     * Create an iFrame for the kitCommand response
     *
     * @param Application $app
     */
    public function ControllerCreateIFrame(Application $app)
    {
        $this->initParameters($app);
        return $this->createIFrame('/basic/simulate');
    }
}
