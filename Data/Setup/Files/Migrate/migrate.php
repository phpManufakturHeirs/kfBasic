<?php

/**
 * kitFramework::Migrate
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

define('BOOTSTRAP_PATH', __DIR__);

$url = "http".(!empty($_SERVER['HTTPS'])?"s":"")."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$url = substr($url, 0, strpos($url, '/kit2/')+strlen('/kit2'));
define('BOOTSTRAP_URL', $url);

require_once __DIR__.'/extension/phpmanufaktur/phpManufaktur/Basic/Control/Migrate/bootstrap.include.php';
