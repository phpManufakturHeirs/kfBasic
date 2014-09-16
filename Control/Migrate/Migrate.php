<?php

/**
 * kitFramework::Migrate
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\Basic\Control\Migrate;

use phpManufaktur\Basic\Control\Pattern\Alert;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Migrate extends Alert
{
    protected $Authenticate = null;
    protected static $CMS_PATH = null;
    protected static $CMS_URL = null;

    /**
     * (non-PHPdoc)
     * @see \phpManufaktur\Basic\Control\Pattern\Alert::initialize()
     */
    protected function initialize(Application $app)
    {
        parent::initialize($app);

        $this->Authenticate = new Authenticate($app);

        self::$CMS_PATH = substr(FRAMEWORK_PATH, 0, strpos(FRAMEWORK_PATH, '/kit2'));
        self::$CMS_URL = substr(FRAMEWORK_URL, 0, strpos(FRAMEWORK_URL, '/kit2'));
    }

    /**
     * Get form to check the CMS and kitFramework path and URL
     *
     * @param array $data
     */
    protected function formCheckPathAndUrl($data=array())
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('cms_path', 'text', array(
            'data' => isset($data['cms_path']) ? $data['cms_path'] : ''
        ))
        ->add('cms_url', 'text', array(
            'data' => isset($data['cms_url']) ? $data['cms_url'] : ''
        ))
        ->add('framework_path', 'text', array(
            'data' => isset($data['framework_path']) ? $data['framework_path'] : ''
        ))
        ->add('framework_url', 'text', array(
            'data' => isset($data['framework_url']) ? $data['framework_url'] : ''
        ))
        ->getForm();
    }

    protected function readCMSconfig(&$config=array())
    {
        // check if token is a constant value
        function is_constant($token)
        {
            return $token == T_CONSTANT_ENCAPSED_STRING || $token == T_STRING ||
            $token == T_LNUMBER || $token == T_DNUMBER;
        }

        // strip quotation marks form token value
        function strip($value)
        {
            return preg_replace('!^([\'"])(.*)\1$!', '$2', $value);
        }

        if (false === ($code = @file_get_contents(self::$CMS_PATH.'/config.php'))) {
            $error = error_get_last();
            $this->setAlert('Can not read the file <strong>%file%</strong>!',
                array('%file%' => '/config.php'), self::ALERT_TYPE_DANGER, true,
                array('error' => $error['message'], 'path' => CMS_PATH.'/config.php', 'method' => __METHOD__));
            return false;
        }

        $defines = array();
        $state = 0;
        $key = '';
        $value = '';

        // get all TOKENS from the code
        $tokens = token_get_all($code);
        $token = reset($tokens);
        while ($token) {
            if (is_array($token)) {
                if ($token[0] === T_WHITESPACE || $token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT) {
                    // do nothing
                }
                elseif ($token[0] === T_STRING && strtolower($token[1]) === 'define') {
                    $state = 1;
                }
                elseif ($state === 2 && is_constant($token[0])) {
                    $key = $token[1];
                    $state = 3;
                }
                elseif ($state === 4 && is_constant($token[0])) {
                    $value = $token[1];
                    $state = 5;
                }
            }
            else {
                $symbol = trim($token);
                if ($symbol === '(' && $state === 1) {
                    $state = 2;
                }
                elseif ($symbol === ',' && $state === 3) {
                    $state = 4;
                }
                elseif ($symbol === ')' && $state === 5) {
                    $defines[strip($key)] = strip($value);
                    $state = 0;
                }
            }
            $token = next($tokens);
        }

foreach ($defines as $k => $v) {
    echo "'$k' => '$v'\n";
}

/*
function dump($state, $token) {
    if (is_array($token)) {
        echo "$state: " . token_name($token[0]) . " [$token[1]] on line $token[2]\n";
    } else {
        echo "$state: Symbol '$token'\n";
    }
}
*/

/*
        echo "<pre>";
        print_r($tokens);
        echo "</pre>";*/
    }

    /**
     * This controller start the migration process
     *
     * @param Application $app
     */
    public function ControllerStart(Application $app)
    {
        $this->initialize($app);

        if (!$this->Authenticate->IsAuthenticated()) {
            // the user must first authenticate
            return $this->Authenticate->ControllerAuthenticate($app);
        }

        $this->readCMSconfig();

        $form = $this->formCheckPathAndUrl();

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/migrate/path.and.url.twig'),
            array(
                'alert' => $this->getAlert(),
                'form' => $form->createView()
        ));
    }

    public function ControllerPathAndUrlCheck(Application $app)
    {
        $this->initialize($app);

        if (!$this->Authenticate->IsAuthenticated()) {
            // the user must first authenticate
            return $this->Authenticate->ControllerAuthenticate($app);
        }

        $form = $this->formCheckPathAndUrl();
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();

            // perform the checks

            $this->setAlert ('uh?');
        }
        else {
            // general error (timeout, CSFR ...)
            $this->setAlert('The form is not valid, please check your input and try again!', array(),
                self::ALERT_TYPE_DANGER, true, array('form_errors' => $form->getErrorsAsString(),
                    'method' => __METHOD__, 'line' => __LINE__));
        }

        $subRequest = Request::create('/start/', 'GET');
        return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
