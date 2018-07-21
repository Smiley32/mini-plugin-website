<?php

class Plugins {
  private static $_plugins = null;
  
  private static function _retrieveAllPlugins() {
    // Read plugin file
    $file = fopen('plugins/order.plg', 'r');
    if($file) {
      self::$_plugins = Array();
      while(($line = fgets($file)) !== false) {
        self::$_plugins[] = trim($line);
      }
      fclose($file);
    } else {
      return false;
    }
  }
  
  public static function getAllPluginsInOrder() {
    if(null === self::$_plugins) {
      // Retrieve all plugins
      if(false === self::_retrieveAllPlugins()) {
        return false;
      }
    }
    
    return self::$_plugins; // can be empty
  }
  
  public static function callFunction($pluginName, $function, ...$params) {
    // TODO: check if the plugin exist / the file Export exist
    require_once('plugins/' . $pluginName . '/external/Export.php');
    return Export::$function($params); // Check if the function exist
  }
}

?>