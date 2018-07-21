<?php

class Controller {
  private $_action = null;
  private $_subAction = null;
  private $_isAjax = false;
  private $_useCorrespondingHtml = true;
  private $_scripts = array();
  private $_styles = array();
  private $_title = 'Default title';
  
  private $_reserved = array();
  
  public $data = array();
  
  
  function __construct($action, $subAction = null) {
    $this->_action = $action;
    $this->_subAction = $subAction;
  }
  
  public function call() {
    $fct = 'action_' . $this->_action;
    if(null !== $this->_subAction) {
      $fct($this->_subAction);
    } else {
      $this->$fct();
    }
  }
  
  public function useCorrespondingHtml($use = null) {
    if(null !== $use) {
      $this->_useCorrespondingHtml = $use;
    }
    return $this->_useCorrespondingHtml;
  }
  
  public function setTitle($title) {
    $this->_title = $title;
  }
  
  public function getTitle() {
    return $this->_title;
  }
  
  public function setAjax($ajax) {
    $this->_isAjax = $ajax;
  }
  
  public function isAjax() {
    return $this->_isAjax;
  }
  
  public function getReserved() {
    return $this->_reserved;
  }
  
  /// Filename, not filepath
  public function addScript($fileName) {
    $this->_scripts[] = $fileName;
  }
  
  public function getScripts() {
    return $this->_scripts;
  }
  
  // Filename, not filepath
  public function addStyle($fileName) {
    $this->_styles[] = $fileName;
  }
  
  public function getStyles() {
    return $this->_styles;
  }
}

?>