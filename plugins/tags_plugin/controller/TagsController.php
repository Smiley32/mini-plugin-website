<?php

class TagsController extends Controller {
  protected function action_tagger() {
    $this->addScript('tagger.js');
    $this->addStyle('tagger.css');

    $this->data['categories'] = array();

    $categories = Plugins::callFunction('database_plugin', 'getCategories');
    if($categories) {
      foreach($categories as $category) {
        $this->data['categories'][]['name'] = ucfirst($category['category']);
      }
    }
  }

  protected function action_api() {
    $this->setAjax(true);

    if(isset($_GET['categories']) && $_GET['categories'] == 1) {
      $categories = Plugins::callFunction('database_plugin', 'getCategories');
      $this->_reserved['body'] = json_encode($categories);
    } elseif(isset($_GET['category']) && $_GET['category'] != false) {
      if(isset($_GET['search'])) {
        // Search tags in a category
        $tags = Plugins::callFunction('database_plugin', 'searchTagInCategory', $_GET['category'], $_GET['search']);
        $this->_reserved['body'] = json_encode($tags);
      } elseif(isset($_GET['post']) && false != $_GET['post']) {
        $tags = Plugins::callFunction('database_plugin', 'getTagsInCategoryByPost', $_GET['category'], $_GET['post']);
        $this->_reserved['body'] = json_encode($tags);
      } elseif(isset($_GET['tag']) && '' != $_GET['tag']) {
        $ret = Plugins::callFunction('database_plugin', 'addTagInCategory', $_GET['category'], $_GET['tag']);
        if($ret) {
          $this->_reserved['body'] = '{"error": 0}';
        } else {
          $this->_reserved['body'] = '{"error": 1}';
        }
      }
    }
  }
}

?>
