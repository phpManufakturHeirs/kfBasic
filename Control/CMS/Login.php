<?php

use phpManufaktur\Basic\Data\CMS\Groups;
/**
 * kitFramework::kfBasic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// need config.php of the CMS
require_once(CMS_PATH.'/config.php');

function cmsLoginUser($user)
{
    global $app;

    $_SESSION['USER_ID'] = $user['user_id'];
    $_SESSION['GROUP_ID'] = $user['group_id'];
    $_SESSION['GROUPS_ID'] = $user['groups_id'];
    $_SESSION['USERNAME'] = $user['username'];
    $_SESSION['DISPLAY_NAME'] = $user['display_name'];
    $_SESSION['EMAIL'] = $user['email'];
    $_SESSION['HOME_FOLDER'] = $user['home_folder'];

    // Set language
    if($user['language'] != '') {
        $_SESSION['LANGUAGE'] = $user['language'];
    }

    // Set timezone
    if ($user['timezone_string'] != '') {
        $_SESSION['TIMEZONE_STRING'] = $user['timezone_string'];
    }
    $timezone_string = (isset ($_SESSION['TIMEZONE_STRING']) ? $_SESSION['TIMEZONE_STRING'] : DEFAULT_TIMEZONESTRING );
    date_default_timezone_set($timezone_string);

    // Set date format
    if($user['date_format'] != '') {
        $_SESSION['DATE_FORMAT'] = $user['date_format'];
    } else {
        // Set a session var so apps can tell user is using default date format
        $_SESSION['USE_DEFAULT_DATE_FORMAT'] = true;
    }
    // Set time format
    if($user['time_format'] != '') {
        $_SESSION['TIME_FORMAT'] = $user['time_format'];
    } else {
        // Set a session var so apps can tell user is using default time format
        $_SESSION['USE_DEFAULT_TIME_FORMAT'] = true;
    }

    // Get group information
    $_SESSION['SYSTEM_PERMISSIONS'] = array();
    $_SESSION['MODULE_PERMISSIONS'] = array();
    $_SESSION['TEMPLATE_PERMISSIONS'] = array();
    $_SESSION['GROUP_NAME'] = array();

    $first_group = true;
    $Groups = new Groups($app);

    foreach (explode(",", $_SESSION['GROUPS_ID']) as $cur_group_id)
    {
        try {
            if (false === ($results_array = $Groups->selectGroup($cur_group_id))) {
                return 'no result';
            }
        } catch (\Exception $e) {
            throw new $e->getMessage();
        }
        $_SESSION['GROUP_NAME'][$cur_group_id] = $results_array['name'];

        // Set system permissions
        $_SESSION['SYSTEM_PERMISSIONS'] = array_merge($_SESSION['SYSTEM_PERMISSIONS'], explode(',', $results_array['system_permissions']));

        // Set module permissions
        if ($first_group) {
            $_SESSION['MODULE_PERMISSIONS'] = explode(',', $results_array['module_permissions']);
        } else {
            $_SESSION['MODULE_PERMISSIONS'] = array_intersect($_SESSION['MODULE_PERMISSIONS'], explode(',', $results_array['module_permissions']));
        }

        // Set template permissions
        if ($first_group) {
            $_SESSION['TEMPLATE_PERMISSIONS'] = explode(',', $results_array['template_permissions']);
        } else {
            $_SESSION['TEMPLATE_PERMISSIONS'] = array_intersect($_SESSION['TEMPLATE_PERMISSIONS'], explode(',', $results_array['template_permissions']));
        }
        $first_group = false;
    }

}