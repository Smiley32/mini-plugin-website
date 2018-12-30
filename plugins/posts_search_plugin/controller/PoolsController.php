<?php

class PoolsController extends Controller {
  protected function action_api() {
    $this->setAjax(true);
    $this->_reserved['body'] = '';

    $model = $this->getModel();
    if(isset($_GET['get']) && $_GET['get'] == 'all') {
      // Get all pools
      $pools = $model->getCurrentUserPools();

      if(!$pools) {
        $this->_reserved['body'] = '';
      } else {
        $this->_reserved['body'] = json_encode($pools);
      }
    } elseif(isset($_GET['add'], $_GET['post'], $_GET['pool']) && $_GET['add'] == 1) {
      $model->addPostInPool($_GET['pool'], $_GET['post']);
    } elseif(isset($_GET['new']) && $_GET['new'] != '') {
      $model->addPool($_GET['new']);
    }
  }

  protected function action_search() {
    $this->addScript('search.js');

    $this->data['pools'] = array();

    if(isset($_GET['s'])) {
      $search = $_GET['s'];
    } else {
      $search = '';
    }

    $model = $this->getModel();
    $pools = $model->searchPools($search);

    if($pools) {
      $this->data['pools'] = $pools;
    }
  }
}

?>
