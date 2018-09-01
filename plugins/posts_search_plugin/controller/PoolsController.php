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
    }
  }

  protected function action_search() {

  }
}

?>
