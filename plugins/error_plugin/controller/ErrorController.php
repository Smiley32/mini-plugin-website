<?php

class ErrorController extends Controller {
  function __construct($action, $subAction) {
    parent::__construct($action, $subAction);
  }
  
  protected function action_404() {
    
  }
  
  protected function action_unknown() {
    $this->data['errno'] = Plugins::callFunction('error_plugin', 'getErrorNumber', -42);
  }
}

?>