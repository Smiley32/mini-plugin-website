<?php

class Export_users_plugin {
  /// $params[0]: pseudo
  /// $params[1]: password
  public static function connect($params) {
    $pseudo = $params[0];
    $password = $params[1];

    if(false != self::getCurrentUser(null)) {
      // TODO: handle error
      return 1;
    }

    // Get user
    $ret = Plugins::callFunction('database_plugin', 'getUser', $pseudo, $password);
    if(!$ret) {
      return 2;
    }

    $_SESSION['id'] = $ret['id'];
    $_SESSION['pseudo'] = $ret['pseudo'];
    $_SESSION['favorites'] = $ret['favorites'];

    return true;
  }

  /// $params should be empty
  public static function disconnect($params) {
    if(!isset($_SESSION['id'])) {
      return false;
    }
    unset($_SESSION['id']);
    unset($_SESSION['pseudo']);
    unset($_SESSION['favorites']);
    return true;
  }

  /// $params[0]: user id XX -> nothing, give an id is useless
  public static function isConnected($params) {
    return isset($_SESSION['id']);
  }

  public static function getCurrentUser($params) {
    if(!isset($_SESSION['id'])) {
      return false;
    }
    $user = array();
    $user['id'] = $_SESSION['id'];
    $user['pseudo'] = $_SESSION['pseudo'];
    $user['favorites'] = $_SESSION['favorites'];
    return $user;
  }
}

?>
