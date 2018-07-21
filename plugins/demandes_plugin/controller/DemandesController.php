<?php

class Db {
  private static $instance = NULL;
  private function __construct() {}
  private function __clone() {}
  public static function getInstance() {
    if(!isset(self::$instance)) {
      $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
      self::$instance = new PDO('mysql:host=localhost;dbname=demandes', 'root', '', $pdo_options);
    }
    return self::$instance;
  }
}

class DemandesController extends Controller {
  function __construct($action, $subAction) {
    parent::__construct($action, $subAction);
  }
  
  protected function action_show() {
    $this->setTitle('{>Requests<}');
    
    $this->addScript('show.js');
    $this->addScript('materialize.min.js');
    
    $this->addStyle('materialize.min.css');
    $this->addStyle('app.css');
  }
  
  protected function action_api() {
    $this->setAjax(true);
    $db = Db::getInstance();
    
    if(isset($_GET['add'])) {
      $req = $db->prepare('INSERT INTO request(description) VALUES (:desc)');
      $ret = $req->execute(array('desc' => $_GET['add']));

      $this->reserved['body'] = json_encode($ret);
    } elseif(isset($_GET['remove'])) {
      $req = $db->prepare('DELETE FROM request WHERE id=:id');
      $ret = $req->execute(array('id' => $_GET['remove']));

      $this->reserved['body'] = json_encode($ret);
    } else {
      $req = $db->prepare('SELECT * FROM request ORDER BY id DESC');
      $ret = $req->execute();

      $this->reserved['body'] = json_encode($req->fetchAll());
    }
  }
}

?>