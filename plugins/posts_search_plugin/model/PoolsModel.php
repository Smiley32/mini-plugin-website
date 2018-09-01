<?php

class PoolsModel extends Database {

  public function searchPools($query) {
    $user = Plugins::callFunction('users_plugin', 'getCurrentUser');

    if(!$user) {
      $user['id'] = -1;
    }

    $db = $this->getInstance();
    $req = $db->prepare('SELECT pools.*, users.pseudo FROM pools, users WHERE (pools.creator=:userId OR pools.private=0) AND pools.creator=users.id ORDER BY pools.title ASC');
    $ret = $req->execute(array('userId' => $user['id']));

    if(!$ret) {
      return false;
    }

    return $req->fetchAll();
  }

  public function getCurrentUserPools() {
    // Get current connected user
    $user = Plugins::callFunction('users_plugin', 'getCurrentUser');

    if(!$user) {
      return false;
    }

    $db = $this->getInstance();

    $req = $db->prepare('SELECT pools.id, pools.title FROM pools WHERE pools.creator=:userId');
    $ret = $req->execute(array('userId' => $user['id']));

    return !$ret ? false : $req->fetchAll();
  }

  public function addPostInPool($poolId, $postId) {
    $postId = (int)$postId;
    $poolId = (int)$poolId;

    $user = Plugins::callFunction('users_plugin', 'getCurrentUser');

    if(!$user) {
      return false;
    }

    $db = $this->getInstance();

    $req = $db->prepare('SELECT * FROM pools WHERE id=:poolId && creator=:userId');
    $ret = $req->execute(array('poolId' => $poolId, 'userId' => $user['id']));

    if(!$ret) {
      return false;
    }

    $fetched = $req->fetch();
    if(!$fetched) {
      return false;
    }

    $req = $db->prepare('INSERT INTO pool_post (post_id, pool_id) VALUES (:post_id, :pool_id)');
    $ret = $req->execute(array('post_id' => $postId, 'pool_id' => $poolId));

    if(!$ret) {
      return false;
    }

    // ...

    return true;
  }
}

?>
