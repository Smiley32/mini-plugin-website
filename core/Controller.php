<?php

class Controller {
  private $_controller = null;
  protected $_action = null;
  protected $_subAction = null;
  private $_isAjax = false;
  private $_useCorrespondingHtml = true;
  private $_scripts = array();
  private $_styles = array();
  private $_title = 'Default title';
  private $_model = null;

  protected $_reserved = array();

  public $data = array();

  public function __construct($controller, $action, $subAction = null) {
    $this->_controller = $controller;
    $this->_action = $action;
    $this->_subAction = $subAction;
    $this->data['error'] = false;
    $this->data['errors'] = array();
  }

  public function addError($description) {
    $this->data['error'] = true;
    $this->data['errors'][]['description'] = $description;
  }

  public function checkGet($param) {
    if(!isset($_GET[$param]) || $_GET[$param] == false) {
      $this->addError($param . ' is empty');
      return false;
    }
    return $_GET[$param]; // TODO: protect
  }

  public function call() {
    $fct = 'action_' . $this->_action;
    if(null !== $this->_subAction) {
      $fct($this->_subAction);
    } else {
      $this->$fct();
    }
  }

  protected function getModel() {
    if(null == $this->_model) {
      $class = ucfirst($this->_controller) . 'Model';
      $this->_model = new $class();
    }
    return $this->_model;
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
