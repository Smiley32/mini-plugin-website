<?php

class LayoutController extends Controller {
  protected function action_default() {
    // Do whatever you want for your website

    $this->addScript('functions.js');
    $this->addStyle('bulma.min.css');

    // Will be set after:
    // $data['body'] -> content of the others views
    // $data['title'] -> page title
    // $data['scripts'] -> scripts js
    // $data['styles'] -> styles css
  }
}

?>
