<?php

class PostsController extends Controller {
  function __construct($action, $subAction) {
    parent::__construct($action, $subAction);
  }

  protected function action_api() {
    $this->setAjax(true);

    if(isset($_GET['favorite']) && '' != $_GET['favorite']) {
      $favorite = $_GET['favorite'];

      $user = Plugins::callFunction('users_plugin', 'getCurrentUser');
      if(false == $user) {
        $this->_reserved['body'] = '{"error": 1}';
        return;
      }

      $pool = $user['favorites'];
      $model = $this->getModel();
      $isFavorite = $model->postIsInPool($pool, $favorite);

      if($isFavorite) {
        Plugins::callFunction('database_plugin', 'removePostFromPool', $pool, $favorite);
      } else {
        Plugins::callFunction('database_plugin', 'addPostInPool', $pool, $favorite);
      }
    }

    $this->_reserved['body'] = '{"error": 0}';
  }

  protected function action_search() {
    $tags = '';
    $page = 1;

    $this->addScript('search.js');
    $this->addStyle('search.css');

    // TODO: protect!!!
    if(isset($_GET['tags'])) {
      $tags = $_GET['tags'];
    }

    if(isset($_GET['page'])) {
      if($_GET['page'] > 0) {
        $page = $_GET['page'];
      }
    }

    $posts = Plugins::callFunction('database_plugin', 'getPosts', $tags, $page);

    $this->data['isConnected'] = Plugins::callFunction('users_plugin', 'isConnected');
    $user = Plugins::callFunction('users_plugin', 'getCurrentUser');

    $this->data['list'] = array();
    $i = 0;
    foreach($posts as $post) {
      $this->data['list'][$i]['id'] = $post['id'];
      $this->data['list'][$i]['hash'] = $post['hash'];
      $this->data['list'][$i]['width'] = $post['width'];
      $this->data['list'][$i]['height'] = $post['height'];
      $this->data['list'][$i]['uploader'] = 'Smiley32'; // TODO: ...
      if(false != $user && $this->getModel()->postIsInPool($user['favorites'], $post['id'])) {
        $this->data['list'][$i]['favorite'] = 'loved';
      } else {
        $this->data['list'][$i]['favorite'] = '';
      }
      $i++;
    }

    $this->data['previousPage'] = $page > 1 ? $page - 1 : $page;
    $this->data['nextPage'] = $page + 1;

  }
}

?>
