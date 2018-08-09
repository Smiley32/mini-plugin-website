<?php

class Page {
  private $_views = array();

  function __construct() {

  }

  public function addView($view) {
    $this->_views[] = $view;
  }

  public function display() {
    // Check if one page is ajax. If it's the case, display only this one
    // Get the content of the first view (it will include the content of the others)
    // Get all script/css and include them in the page
    // Display the final page
    if(empty($this->_views)) {
      echo 'rien';
      return true;
    }

    // Ajax check
    foreach($this->_views as $view) {
      if($view->isAjax()) {
        echo $view->getContent();
        return true;
      }
    }

    // Get the content of the views
    $data['body'] = $this->_views[0]->getContent();
    $data['title'] = $this->_views[0]->getTitle();
    $data['scripts'] = array();
    $data['styles'] = array();

    // Load global layout
    $v = new View('layout.html', null);
    $v->_replaceRoutes();

    $iScript = 0;
    $iStyle = 0;
    // Get all the scripts and styles
    foreach($this->_views as $view) {
      $scripts = $view->getScripts();
      foreach($scripts as $script) {
        $data['scripts'][$iScript]['path'] = '/plugins/' . $view->getPlugin()->getName() . '/view/js/' . $script;
        $iScript++;
      }

      $styles = $view->getStyles();
      foreach($styles as $style) {
        $data['styles'][$iStyle]['path'] = '/plugins/' . $view->getPlugin()->getName() . '/view/css/' . $style;
        $iStyle++;
      }
    }

    $v->compile($data);
    echo $v->getContent();
    return true;
  }
}

?>
