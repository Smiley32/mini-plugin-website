<?php

class Database {
  private $_instance = null;

  protected function getInstance() {
    if(!isset($this->_instance)) {
      $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
      $this->_instance = new PDO('mysql:host=localhost;dbname=website', 'root', '', $pdo_options);
    }
    return $this->_instance;
  }


}

?>
