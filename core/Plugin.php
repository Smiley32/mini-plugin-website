<?php

class Plugin {
  private $_name = null;
  private $_strings = null;
  private $_class = null;
  
  function __construct($name) {
    $this->_name = $name;
  }
  
  public function getStrings() {
    if(null === $this->_strings) {
      $language = 'english'; // TODO: not here
      $file = fopen('plugins/' . $this->_name . '/view/strings/' . $language . '.str', 'r');
      if($file) {
        $this->_strings = array();
        while(false !== ($line = fgets($file))) {
          if(preg_match('/\s*([_a-zA-Z0-9]+)\s*:\s*(.+)/', $line, $matches) === 1) {
            $this->_strings[$matches[1]] = $matches[2];
          }
        }
      }
    }    
    return null === $this->_strings ? false : $this->_strings;
  }
  
  public function call($controller, $action, $subAction = null) {
    require_once('core/Controller.php');
    
    $class = ucfirst($controller) . 'Controller';
    require_once('plugins/' . $this->_name . '/controller/' . $class . '.php');
    
    $this->_class = new $class($action, $subAction);
    $this->_class->call();
  }
  
  public function compileView($controller, $action, $subAction = null) {
    require_once('core/View.php');
    if($this->_class->isAjax()) {
      $v = new View($this->_class->reserved['body'], $this, false);
      Settings::getCurrentPage()->addView($v);
      $v->setAjax(true);
    } else {
      $reserved = $this->_class->getReserved();
      if($this->_class->useCorrespondingHtml()) {
        $v = new View('plugins/' . $this->_name . '/view/html/' . $controller . '/' . $action . '.html', $this);
      } else {
        $v = new View('{{body}}', $this, false);
        if(!isset($reserved['body'])) {
          $reserved['body'] = '';
        }
      }
      Settings::getCurrentPage()->addView($v);
      $v->compile(array_merge($this->_class->getReserved(), $this->_class->data));
      $v->setAjax($this->_class->isAjax());
      $v->setScripts($this->_class->getScripts());
      $v->setStyles($this->_class->getStyles());
      $v->setTitle($this->_class->getTitle());
    }
  }
  
  public function display($controller, $action, $subAction = null) {
    require_once('core/View.php');
    if($this->_class->isAjax()) {
      $v = new View($this->_class->reserved['body'], $this, false);
      Settings::getCurrentPage()->addView($v);
      $v->setAjax(true);
      echo $v->getContent();
    } else {
      $reserved = $this->_class->getReserved();
      if($this->_class->useCorrespondingHtml()) {
        $v = new View('plugins/' . $this->_name . '/view/html/' . $controller . '/' . $action . '.html', $this);
      } else {
        $v = new View('{{body}}', $this, false);
        if(!isset($reserved['body'])) {
          $reserved['body'] = '';
        }
      }
      Settings::getCurrentPage()->addView($v);
      $v->compile(array_merge($reserved, $this->_class->data));
      $v->setAjax($this->_class->isAjax());
      $v->setScripts($this->_class->getScripts());
      $v->setStyles($this->_class->getStyles());
      $v->setTitle($this->_class->getTitle());
      echo $v->getContent();
    }
  }
  
  public function getName() {
    return $this->_name;
  }
}

?>