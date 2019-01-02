<?php

class TagsController extends Controller {
  protected function action_tagger() {
    $this->addScript('tagger.js');
    $this->addStyle('tagger.css');

    $this->data['categories'] = array();

    $model = $this->getModel();
    $categories = $model->getCategories();
    if($categories) {
      foreach($categories as $category) {
        $this->data['categories'][]['name'] = ucfirst($category['category']);
      }
    }
  }

  protected function action_api() {
    $this->setAjax(true);

    $model = $this->getModel();

    if(isset($_GET['categories']) && $_GET['categories'] == 1) {
      $categories = Plugins::callFunction('database_plugin', 'getCategories');
      $this->_reserved['body'] = json_encode($categories);
    } elseif(isset($_GET['category']) && $_GET['category'] != false) {
      if(isset($_GET['search'])) {
        // Search tags in a category
        $tags = $model->searchTagInCategory($_GET['category'], $_GET['search']);
        $this->_reserved['body'] = json_encode($tags);
      } elseif(isset($_GET['post']) && false != $_GET['post']) {
        $tags = $model->getTagsInCategoryByPost($_GET['category'], $_GET['post']);
        $this->_reserved['body'] = json_encode($tags);
      } elseif(isset($_GET['tag']) && '' != $_GET['tag']) {
        $ret = $model->addTagInCategory($_GET['category'], $_GET['tag']);
        if($ret) {
          $this->_reserved['body'] = '{"error": 0}';
        } else {
          $this->_reserved['body'] = '{"error": 1}';
        }
      }
    } elseif(isset($_GET['remove'], $_GET['post']) && $_GET['remove'] != '' && $_GET['post'] != '') {
      $ret = $model->removeTagFromPost($_GET['post'], $_GET['remove']);
      if($ret) {
        $this->_reserved['body'] = '{"error": 0}';
      } else {
        $this->_reserved['body'] = '{"error": 1}';
      }
    } else {
      $this->_reserved['body'] = '{"error": 42}';
    }
  }
}

?>
