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
    protected function formUrlCheck($data=array())
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('existing_cms_url', 'hidden', array(
            'data' => isset($data['existing_cms_url']) ? $data['existing_cms_url'] : ''
        ))
        ->add('cms_url', 'text', array(
            'data' => self::$CMS_URL
        ))
        ->getForm();
    }

    protected function formMySqlCheck($data=array())
    {
        return $this->app['form.factory']->createBuilder('form')
        ->add('cms_url_changed', 'hidden', array(
            'data' => isset($data['cms_url_changed']) ? $data['cms_url_changed'] : null
        ))
        ->add('existing_cms_url', 'hidden', array(
            'data' => isset($data['existing_cms_url']) ? $data['existing_cms_url'] : null
        ))
        ->add('cms_url', 'hidden', array(
            'data' => isset($data['cms_url']) ? $data['cms_url'] : null
        ))
        ->add('existing_db_host', 'hidden', array(
            'data' => isset($data['existing_db_host']) ? $data['existing_db_host'] : null
        ))
        ->add('db_host', 'text', array(
            'data' => isset($data['db_host']) ? $data['db_host'] : null
        ))
        ->add('existing_db_port', 'hidden', array(
            'data' => isset($data['existing_db_port']) ? $data['existing_db_port'] : null
        ))
        ->add('db_port', 'text', array(
            'data' => isset($data['db_port']) ? $data['db_port'] : null
        ))
        ->add('existing_db_name', 'hidden', array(
            'data' => isset($data['existing_db_name']) ? $data['existing_db_name'] : null
        ))
        ->add('db_name', 'text', array(
            'data' => isset($data['db_name']) ? $data['db_name'] : null
        ))
        ->add('existing_db_username', 'hidden', array(
            'data' => isset($data['existing_db_username']) ? $data['existing_db_username'] : null
        ))
        ->add('db_username', 'text', array(
            'data' => isset($data['db_username']) ? $data['db_username'] : null
        ))
        ->add('existing_db_password', 'hidden', array(
            'data' => isset($data['existing_db_password']) ? $data['existing_db_password'] : null
        ))
        ->add('db_password', 'password', array(
            'data' => isset($data['db_password']) ? $data['db_password'] : null,
            'required' => false
        ))
        ->add('existing_table_prefix', 'hidden', array(
            'data' => isset($data['existing_table_prefix']) ? $data['existing_table_prefix'] : null
        ))
        ->add('table_prefix', 'text', array(
            'data' => isset($data['table_prefix']) ? $data['table_prefix'] : null
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

        $config = $defines;
        return true;
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

        $next_step = true;

        $config = array();
        if (!$this->readCMSconfig($config)) {
            $next_step = false;
        }

        $cms_url = null;
        if (isset($config['WB_URL'])) {
            $cms_url = $config['WB_URL'];
        }
        elseif (isset($config['CAT_URL'])) {
            $cms_url = $config['CAT_URL'];
        }
        else {
            $cms_url = null;
        }

        $data = array(
            'existing_cms_url' => $cms_url,
        );
        $form = $this->formUrlCheck($data);

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/migrate/url.twig'),
            array(
                'alert' => $this->getAlert(),
                'form' => $form->createView(),
                'next_step' => $next_step
        ));
    }

    /**
     * Controller to check the CMS URL
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ControllerUrlCheck(Application $app)
    {
        $this->initialize($app);

        if (!$this->Authenticate->IsAuthenticated()) {
            // the user must first authenticate
            return $this->Authenticate->ControllerAuthenticate($app);
        }

        $form = $this->formUrlCheck();
        $form->bind($this->app['request']);

        if ($form->isValid()) {
            // the form is valid
            $data = $form->getData();

            $checked = true;
            if (!filter_var($data['cms_url'])) {
                $this->setAlert('The URL <strong>%url%</strong> is not valid, please check your input!',
                    array('%url%' => $data['cms_url']), self::ALERT_TYPE_DANGER);
                $checked = false;
            }

            if ($checked) {
                $changes = array(
                    'cms_url_changed' => ($data['existing_cms_url'] !== $data['cms_url']),
                    'existing_cms_url' => $data['existing_cms_url'],
                    'cms_url' => $data['cms_url']
                );

                $subRequest = Request::create('/mysql/', 'POST', $changes);
                return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            }
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

    public function ControllerMySql(Application $app)
    {
        $this->initialize($app);

        if ((null === ($cms_url_changed = $app['request']->get('cms_url_changed'))) ||
            (null === ($existing_cms_url = $app['request']->get('existing_cms_url'))) ||
            (null === ($cms_url = $app['request']->get('cms_url')))) {
            // invalid submission
            throw new \Exception('Missing one or more POST data!');
        }

        $next_step = true;

        $config = array();
        if (!$this->readCMSconfig($config)) {
            $next_step = false;
        }

        $data = array();
        if ($next_step) {
            $data = array(
                'cms_url_changed' => $cms_url_changed,
                'existing_cms_url' => $existing_cms_url,
                'cms_url' => $cms_url,
                'existing_db_host' => isset($config['CAT_DB_HOST']) ? $config['CAT_DB_HOST'] : $config['DB_HOST'],
                'db_host' => isset($config['CAT_DB_HOST']) ? $config['CAT_DB_HOST'] : $config['DB_HOST'],
                'existing_db_port' => isset($config['CAT_DB_PORT']) ? $config['CAT_DB_PORT'] : $config['DB_PORT'],
                'db_port' => isset($config['CAT_DB_PORT']) ? $config['CAT_DB_PORT'] : $config['DB_PORT'],
                'existing_db_name' => isset($config['CAT_DB_NAME']) ? $config['CAT_DB_NAME'] : $config['DB_NAME'],
                'db_name' => isset($config['CAT_DB_NAME']) ? $config['CAT_DB_NAME'] : $config['DB_NAME'],
                'existing_db_username' => isset($config['CAT_DB_USERNAME']) ? $config['CAT_DB_USERNAME'] : $config['DB_USERNAME'],
                'db_username' => isset($config['CAT_DB_USERNAME']) ? $config['CAT_DB_USERNAME'] : $config['DB_USERNAME'],
                'existing_db_password' => isset($config['CAT_DB_PASSWORD']) ? $config['CAT_DB_PASSWORD'] : $config['DB_PASSWORD'],
                'db_password' => isset($config['CAT_DB_PASSWORD']) ? $config['CAT_DB_PASSWORD'] : $config['DB_PASSWORD'],
                'existing_table_prefix' => isset($config['CAT_TABLE_PREFIX']) ? $config['CAT_TABLE_PREFIX'] : $config['TABLE_PREFIX'],
                'table_prefix' => isset($config['CAT_TABLE_PREFIX']) ? $config['CAT_TABLE_PREFIX'] : $config['TABLE_PREFIX']
            );
        }

        $form = $this->formMySqlCheck($data);

        return $app['twig']->render($app['utils']->getTemplateFile(
            '@phpManufaktur/Basic/Template', 'framework/migrate/mysql.twig'),
            array(
                'alert' => $this->getAlert(),
                'form' => $form->createView(),
                'next_step' => $next_step
        ));
    }

    public function ControllerMySqlCheck(Application $app)
    {
        $this->initialize($app);

        return __METHOD__;
    }
}
