<?php

class Route {
  private $_controller = null;
  private $_action = null;
  private $_acceptPrivate = false;
  private $_plugin = null;

  private $_class = null;

  function __construct($controller, $action, $acceptPrivate = false) {
    $this->_controller = $controller;
    $this->_action = $action;

    $this->_acceptPrivate = $acceptPrivate;

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
        while(($line = fgets($file)) !== false) {
          if(preg_match('|\s*([a-zA-Z0-9]+)\s*/\s*([a-zA-Z0-9]+)\s*(?:\[(private)\])?\s*$|', $line, $matches) === 1) {
            if(trim($matches[1]) == $this->_controller && trim($matches[2]) == $this->_action) {
              if(!$this->_acceptPrivate && isset($matches[3]) && $matches[3] == 'private') {
                // This is a private route
                continue;
              }
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
    $this->_plugin->call($this->_controller, $this->_action);
  }

  public function compileView() {
    if($this->_controller === null || $this->_action === null || $this->_plugin === null) {
      // 404
      $route = Settings::get404Route();
      if(preg_match('|\s*([a-zA-Z0-9]+)\s*/\s*([a-zA-Z0-9]+)\s*$|', $route, $matches) === 1) {
        $this->_controller = $matches[1];
        $this->_action = $matches[2];
        $this->_findRoute();
        $this->call();
        if($this->_plugin !== null) {
          $this->_plugin->compileView($this->_controller, $this->_action);
        } else {
          echo 'error 404 - page not found';
        }
      } else {
        echo 'error 404 - page not found';
      }
      return false;
    }
    $this->_plugin->compileView($this->_controller, $this->_action);
  }

  public function display($customData = null) {
    if($this->_controller === null || $this->_action === null || $this->_plugin === null) {
      // 404
      $route = Settings::get404Route();
      if(preg_match('|\s*([a-zA-Z0-9]+)\s*/\s*([a-zA-Z0-9]+)\s*$|', $route, $matches) === 1) {
        $this->_controller = $matches[1];
        $this->_action = $matches[2];
        $this->_findRoute();
        $this->call();
        if($this->_plugin !== null) {
          $this->_plugin->display($this->_controller, $this->_action, $customData);
        } else {
          echo 'error 404 - page not found';
        }
      } else {
        echo 'error 404 - page not found';
      }
    }
    $this->_plugin->display($this->_controller, $this->_action, $customData);
  }
}

?>
