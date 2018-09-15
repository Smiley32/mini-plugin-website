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

    $views = $this->_views;
    $this->_views = array();

    $rt = Settings::getSetting('layout-route');
    if(!$rt) {
      $rt = 'layout/default';
    }
    $controller = null;
    $action = null;

    $length = strlen($rt);
    for($i = 0; $i < $length; $i++) {
      if($rt[$i] == '/') {
        $controller = substr($rt, 0, $i);
        $action = substr($rt, $i + 1);
      }
    }

    $layoutRoute = new Route($controller, $action, true);
    $layoutRoute->call();
    $layoutRoute->compileView();

    $this->_views = array_merge($this->_views, $views);

    // Ajax check
    foreach($this->_views as $view) {
      if($view->isAjax()) {
        echo $view->getContent();
        return true;
      }
    }

    // Get the content of the views
    $data['body'] = $views[0]->getContent();
    $data['title'] = $views[0]->getTitle();
    $data['scripts'] = array();
    $data['styles'] = array();

    $iScript = 0;
    $iStyle = 0;
    // Get all the scripts and styles
    foreach($this->_views as $view) {
      $scripts = $view->getScripts();
      foreach($scripts as $script) {
        $data['scripts'][$iScript]['path'] = '[[plugins]]/' . $view->getPlugin()->getName() . '/view/js/' . $script;
        $iScript++;
      }

      $styles = $view->getStyles();
      foreach($styles as $style) {
        $data['styles'][$iStyle]['path'] = '[[plugins]]/' . $view->getPlugin()->getName() . '/view/css/' . $style;
        $iStyle++;
      }
    }

    // $layoutRoute->compileView();
    $layoutRoute->display($data);

    return true;
  }
}

?>
