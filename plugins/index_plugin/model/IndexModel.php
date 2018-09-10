<?php

class IndexModel extends Database {
  public function getPostsCount() {
    $db = $this->getInstance();
    $req = $db->prepare('SELECT COUNT(1) AS count FROM posts');
    $ret = $req->execute();

    if(!$ret) {
      return false;
    }

    return $req->fetch();
  }
}

?>
