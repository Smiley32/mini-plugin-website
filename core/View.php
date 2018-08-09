<?php

class View {
  private $_content = null;
  private $_plugin = null;

  private $_title = '';
  private $_isAjax = false;
  private $_scripts = array();
  private $_styles = array();

  function __construct($file, $plugin, $isFromPath = true) {
    $this->_plugin = $plugin;
    if($isFromPath) {
      $this->_content = file_get_contents($file);
      if(false === $this->_content) {
        $this->_content = null;
      }
    } else {
      $this->_content = $file;
    }
  }

  public function setTitle($title) {
    $this->_title = $title;
  }

  public function getTitle() {
    if(null !== $this->_plugin) {
      $strings = $this->_plugin->getStrings();
      $this->_title =  preg_replace_callback(
        '/{>([_a-zA-Z0-9]+)<}/'
        , function($m) use ($strings) {
          return (string) $strings[$m[1]];
        }, $this->_title
      );
    }
    return $this->_title;
  }

  public function getPlugin() {
    return $this->_plugin;
  }

  public function setAjax($isAjax) {
    $this->_isAjax = $isAjax;
  }

  public function isAjax() {
    return $this->_isAjax;
  }

  public function setScripts($scripts) {
    $this->_scripts = $scripts;
  }

  /// Filename, not filepath
  public function addScript($fileName) {
    $this->_scripts[] = $fileName;
  }

  public function getScripts() {
    return $this->_scripts;
  }

  public function setStyles($styles) {
    $this->_styles = $styles;
  }

  // Filename, not filepath
  public function addStyles($fileName) {
    $this->_styles[] = $fileName;
  }

  public function getStyles() {
    return $this->_styles;
  }

  public function compile($data) {
    $this->_content = $this->_replaceForeach($data);
    $this->_content = $this->_replaceIf($data);
    $this->_content = $this->_replaceVariables($data);
    if(null !== $this->_plugin) {
      $this->_content = $this->_replaceStrings();
    }
    // $this->_content = $this->_replaceLinks();
    $this->_content = $this->_replaceRoutes();
  }

  public function getContent() {
    return $this->_content === null ? '' : $this->_content;
  }

  public function _replaceRoutes() {
    return preg_replace_callback(
      '|{{([_a-zA-Z0-9]+)/([_a-zA-Z0-9]+)}}|'
      , function($m) {
        $route = new Route($m[1], $m[2], null); // TODO: subAction
        $route->call();
        ob_start();
        $route->display();
        $routeContent = ob_get_contents();
        ob_end_clean();
        return $routeContent;
      }, $this->_content
    );
  }

  private function _replaceLinks() {
    // TODO..
  }

  private function _replaceVariables($data) {
    return preg_replace_callback(
      '/{{([_a-zA-Z0-9]+)}}/'
      , function($m) use ($data) {
        return (string) $data[$m[1]];
      }, $this->_content
    );
  }

  private function _replaceStrings() {
    $strings = $this->_plugin->getStrings();

    return preg_replace_callback(
      '/{>([_a-zA-Z0-9]+)<}/'
      , function($m) use ($strings) {
        return (string) $strings[$m[1]];
      }, $this->_content
    );
  }

  private function _replaceForeach($data) {
    return preg_replace_callback(
      '/\[~(.+?)~\](.*?)\[~~\]/ms'
      , function($m) use ($data) {
        $ret = '';
        foreach($data[$m[1]] as $d) {
          $ret .= preg_replace_callback(
            '/{~([_a-zA-Z0-9]+)~}/'
            , function($m) use ($d) {
              return (string) $d[$m[1]];
            }, $m[2]
          );
        }
        return $ret;
      }, $this->_content
    );
  }

  private function _replaceIf($data) {
    return preg_replace_callback(
      '/{\?(.+?)\?}(!?.*?){\?\?}/ms'
      , function($m) use ($data) {
        if($m[1][0] == '!') {
          $index = substr($m[1], 1);
          if(!$data[$index]) {
            return $m[2];
          } else {
            return '';
          }
        } else {
          if($data[$m[1]]) {
            return $m[2];
          } else {
            return '';
          }
        }
      }, $this->_content
    );
  }
}

?>
