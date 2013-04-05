<?php

/**
 * kitFramework
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://addons.phpmanufaktur.de/extendedWYSIWYG
 * @copyright 2012 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use phpManufaktur\Basic\Control\Setup;

// scan the /Locale directory and add all available languages
try {
	$locale_path = MANUFAKTUR_PATH.'/Basic/Data/Locale';
	if (false === ($lang_files = scandir($locale_path)))
		throw new \Exception(sprintf("Can't read the /Locale directory %s!", $locale_path));
	$ignore = array('.', '..', 'index.php');
	foreach ($lang_files as $lang_file) {
		if (!is_file($locale_path.'/'.$lang_file)) continue;
		if (in_array($lang_file, $ignore)) continue;
		$lang_name = pathinfo($locale_path.'/'.$lang_file, PATHINFO_FILENAME);
		// get the array from the desired file
		$lang_array = include_once $locale_path.'/'.$lang_file;
		// add the locale resource file
		$app['translator'] = $app->share($app->extend('translator', function ($translator, $app) use ($lang_array, $lang_name) {
		    $translator->addResource('array', $lang_array, $lang_name);
		    return $translator;
		}));
	}
}
catch (\Exception $e) {
	throw new \Exception(sprintf('Error scanning the /Locale directory %s.', $locale_path), 0, $e);
}

if (!file_exists(MANUFAKTUR_PATH.'/Extension')) {
    // seems that the framework is not complete initialized!
	  $app->match('/', function (Request $request) use ($app) {
	      $subRequest = Request::create('/admin/setup', $request);
	      $response = $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
	      return $response;
	  });
}

$app->match('/admin/setup', function (Request $request) use ($app) {
    $Setup = new Setup();
    return $Setup->dialogStart();
});

$app->match('admin/setup/start', function (Request $request) use ($app) {
    $Setup = new Setup();
    return $Setup->startSetup();
    })
    ->bind('setup_start');
