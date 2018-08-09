<?php

class NavController extends Controller {

  protected function action_top() {
    $this->addScript('top.js');
    $this->addStyle('nav.css');

    $this->data['isConnected'] = Plugins::callFunction('users_plugin', 'isConnected');
  }

  protected function action_bottom() {
    // ...
  }
}

?>
