<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$controller = "error";
$action = "404";

session_start();

require_once('core/Settings.php');

$url = $_SERVER['REQUEST_URI'];

if(preg_match('|^\s*/?([a-zA-Z0-9]+)(?:/([a-zA-Z0-9]+))?(.*)\s*$|', $url, $matches) === 1) {
  $controller = $matches[1];
  if($matches[2] != '') {
    $action = $matches[2];
  } else {
    $action = 'default';
  }
} else {
  // 404
  $route = Settings::get404Route();
  if(preg_match('|\s*([a-zA-Z0-9]+)\s*/\s*([a-zA-Z0-9]+)\s*$|', $route, $matches) === 1) {
    $controller = $matches[1];
    $action = $matches[2];
  }
}

require_once('core/Route.php');
$route = new Route($controller, $action);
$route->call();
$route->compileView();
Settings::getCurrentPage()->display();

?>
