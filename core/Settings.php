<?php

class Settings {
  private static $_language = null;
  private static $_page = null;
  
  function __construct() {
    
  }
  
  public function getLanguage() {
    return self::$_language === null ? 'english' : self::$_language;
  }
  
  public static function getCurrentPage() {
    if(null === self::$_page) {
      require_once('core/Page.php');
      self::$_page = new Page();
    }
    return self::$_page;
  }
}

?>