<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$controller = "error";
$action = "404";
$subAction = null;

session_start();

if(isset($_GET['controller'], $_GET['action'])) {
  $controller = $_GET['controller'];
  $action = $_GET['action'];
}
if(isset($_GET['subAction'])) {
  $subAction = $_GET['subAction'];
}

require_once('core/Settings.php');
require_once('core/Route.php');
$route = new Route($controller, $action, $subAction);
$route->call();
$route->compileView();
Settings::getCurrentPage()->display();

?>
