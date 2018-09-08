<?php

class Settings {
  private static $_page = null;
  private static $_settings = null;

  private static function _readSettings() {
    self::$_settings = array();
    $file = fopen('settings.conf', 'r');
    if($file) {
      while(($line = fgets($file)) !== false) {
        if(preg_match('|^\s*([-a-zA-Z0-9]+)\s*:\s*([-/a-zA-Z0-9]+)\s*$|', $line, $matches) === 1) {
          self::$_settings[$matches[1]] = $matches[2];
        }
      }
    }
  }

  public static function getCurrentPage() {
    if(null === self::$_page) {
      require_once('core/Page.php');
      self::$_page = new Page();
    }
    return self::$_page;
  }

  public static function redirect($controller, $action) {
    header("Location: /$controller/$action");
    exit();
  }

  public static function getSetting($setting) {
    if(null === self::$_settings) {
      self::_readSettings();
    }

    if(null === self::$_settings || !isset(self::$_settings[$setting])) {
      return false;
    }
    return self::$_settings[$setting];
  }

  public static function getLanguage() {
    return self::getSetting('language');
  }

  public static function get404Route() {
    return self::getSetting('404-route');
  }
}

?>
