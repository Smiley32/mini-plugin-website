<?php

class Route {
  private $_controller = null;
  private $_action = null;
  private $_subAction = null;
  
  private $_plugin = null;
  
  private $_class = null;
  
  function __construct($controller, $action, $subAction = null) {
    $this->_controller = $controller;
    $this->_action = $action;
    $this->_subAction = $subAction;
    
    $this->_findRoute();
  }
  
  private function _findRoute() {
    require_once('core/Plugins.php');
    require_once('core/Plugin.php');
    $plugins = Plugins::getAllPluginsInOrder();
    
    foreach($plugins as $plugin) {
      // Get the routes for the current plugin
      // Read plugin file
      $file = fopen('plugins/' . $plugin . '/routes.rt', 'r');
      if($file) {
        $_plugins = array();
        while(($line = fgets($file)) !== false) {
          if(preg_match('|\s*([a-zA-Z0-9]+)\s*/\s*([a-zA-Z0-9]+)\s*$|', $line, $matches) === 1) { // TODO: handle subAction
            if(trim($matches[1]) == $this->_controller && trim($matches[2]) == $this->_action) {
              $this->_plugin = new Plugin($plugin);
              return true;
            }
          }
        }
        fclose($file);
      } else {
        return false;
      }
    }
  }
  
  /**
	 * Call the right function in the right controller
	 */
  public function call() {
    if($this->_controller === null || $this->_action === null || $this->_plugin === null) {
      return false;
    }
    $this->_plugin->call($this->_controller, $this->_action, $this->_subAction);
  }
  
  public function compileView() {
    if($this->_controller === null || $this->_action === null || $this->_plugin === null) {
      echo 'pas de plugin';
      return false;
    }
    $this->_plugin->compileView($this->_controller, $this->_action, $this->_subAction);
  }
  
  public function display() {
    if($this->_controller === null || $this->_action === null || $this->_plugin === null) {
      echo 'pas de plugin';
      return false;
    }
    $this->_plugin->display($this->_controller, $this->_action, $this->_subAction);
  }
}

?>