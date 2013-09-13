<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */
use phpManufaktur\Basic\Data\Security\Users as frameworkUsers;
use Silex\Application;
use phpManufaktur\Basic\Control\Utils;

/**
 * Check if the user is authenticated
 *
 * @return boolean
 */
function twig_is_authenticated (Application $app)
{
    $token = $app['security']->getToken();
    return !is_null($token);
} // twig_is_authenticated()

/**
 * Get the display name of the authenticated user
 *
 * @throws Twig_Error
 * @return string Ambigous string, mixed>
 */
function twig_user_display_name (Application $app)
{
    try {
        $token = $app['security']->getToken();
        if (is_null($token))
            return 'ANONYMOUS';
            // get user by token

        $user = $token->getUser();
        // get the user record
        $frameworkUsers = new frameworkUsers($app);
        if (false === ($user_data = $frameworkUsers->selectUser($user->getUsername()))) {
            // user not found!
            return 'ANONYMOUS';
        }
        $display_name = (isset($user_data['displayname']) && ! empty($user_data['displayname'])) ? $user_data['displayname'] : $user_data['username'];
        return $display_name;
    } catch (Exception $e) {
        throw new Twig_Error($e->getMessage());
    }
} // twig_user_display_name()

/**
 * Get the template depending on namespace and the framework settings for the template itself
 *
 * @param string $template_namespace
 * @param string $template_file
 * @param string $preferred_template
 * @return string
 */
function twig_template_file($template_namespace, $template_file, $preferred_template='')
{
    return Utils::templateFile($template_namespace, $template_file, $preferred_template);
}

/**
 * Parse the content for kitCommands and execute them
 *
 * @param Application $app
 * @param string $content
 * @return string parsed content
 */
function twig_parse_command(Application $app, $content)
{
    return $app['utils']->parseKITcommand($content);
}

/**
 * Execute a kitCommand with the given parameter
 *
 * @param Application $app
 * @param string $command
 * @param array $parameter
 */
function twig_exec_command(Application $app, $command, array $parameter=array())
{
    return $app['utils']->execKITcommand($command, $parameter);
}

/**
 * Return a ReCaptcha dialog if the ReCaptcha service is active
 *
 * @param Application $app
 */
function twig_recaptcha(Application $app)
{
    return $app['recaptcha']->getHTML();
}

function twig_recaptcha_is_active(Application $app)
{
    return $app['recaptcha']->isActive();
}
