<?php

class Database {
  private static $_instance = null;
  
  public static function getInstance() {
    if(!isset(self::$_instance)) {
      $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
      self::$_instance = new PDO('mysql:host=localhost;dbname=demandes', 'root', '', $pdo_options);
    }
    return self::$_instance;
  }
  
  public static function select($sql, $array) {
    $req = $db->prepare($sql);
    if(!empty($array)) {
      $ret = $req->execute($array);
    } else {
      $ret = $req->execute();
    }
    return $req->fetchAll();
  }
  
  public static function selectSingle($sql, $array) {
    $req = $db->prepare($sql);
    if(!empty($array)) {
      $ret = $req->execute($array);
    } else {
      $ret = $req->execute();
    }
    return $req->fetchAll(); // TODO: fetch one
  }
}

?>