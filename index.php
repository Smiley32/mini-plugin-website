<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$controller = null;
$action = null;

session_start();

require_once('core/Settings.php');
require_once('core/Controller.php');

$indexUrl = $_SERVER['PHP_SELF'];
$indexUrlLength = strlen($indexUrl);

$dirs = array();

$begin = 0;
$end = 0;
for($i = 0; $i < $indexUrlLength; $i++) {
  $end = $i;
  if($indexUrl[$i] == '/') {
    // This file is in a directory
    $dir = substr($indexUrl, $begin, $end - $begin);
    $dirs[] = $dir;

    $begin = $i + 1;
    $end = $begin;
  }
}

$url = $_SERVER['REQUEST_URI'];
$urlLength = strlen($url);

$subdirs = array();

$begin = 0;
$end = 0;
for($i = 0; $i < $urlLength; $i++) {
  $end = $i;
  if($url[$i] == '/') {
    // This file is in a directory
    $dir = substr($url, $begin, $end - $begin);
    $subdirs[] = $dir;

    $begin = $i + 1;
    $end = $begin;
  } elseif($url[$i] == '?') {
    $dir = substr($url, $begin, $end - $begin);
    $subdirs[] = $dir;

    break;
  } elseif($i == $urlLength - 1) {
    $end++;
    $dir = substr($url, $begin, $end - $begin);
    $subdirs[] = $dir;
  }
}

$subdirs = array_diff($subdirs, $dirs);

$i = 0;
foreach($subdirs as $d) {
  if($i == 0) {
    $controller = $d;
  } elseif($i == 1) {
    $action = $d;
  } else {
    break;
  }
  $i++;
}

require_once('core/Route.php');
$route = new Route($controller, $action);
$route->call();
$route->compileView();
Settings::getCurrentPage()->display();

?>
