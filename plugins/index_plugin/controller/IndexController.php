<?php

class IndexController extends Controller {

  protected function action_counter() {
    $postCount = Plugins::callFunction('database_plugin', 'getPostsCount');

    if(false === $postCount) {
      $postCount = 0;
    }

    $this->data['postCount'] = $postCount['count'];

    $this->setTitle($postCount['count'] . ' {>posts<}');
    $this->addScript('odometer.min.js');
    $this->addStyle('odometer-theme-default.css');
  }
}

?>
