<?php

class PostsModel extends Database {
  /// $params[0]: creator
  /// $params[1]: title
  /// $params[2]: description
  /// $params[3]: rating
  /// $params[4]: private
  public function createPool($creatorId, $title, $description, $rating, $private) {
    $db = $this->getInstance();

    $req = $db->prepare('INSERT INTO pools (creator, title, description, rating, private) VALUES (:creator, :title, :description, :rating, :private)');
    $ret = $req->execute(array(
      'creator'     => $creatorId,
      'title'       => $title,
      'description' => $description,
      'rating'      => $rating,
      'private'     => $private
    ));

    if(!$ret) {
      return false;
    }

    return $db->lastInsertId();
  }

  /// $params[0]: pool id
  public function getPool($id) {
    $id = (int)$id;

    $db = $this->getInstance();

    $req = $db->prepare('SELECT * FROM pools WHERE id=:id');
    $ret = $req->execute(array('id' => $id));

    if(!$ret) {
      return false;
    }

    $fetched = $req->fetch();

    if($fetched['private']) {
      $user = Plugins::callFunction('users_plugin', 'getCurrentUser');

      if(!$user || $user['id'] != $fetched['creator']) {
        return false;
      } else {
        return $fetched;
      }
    } else {
      return $fetched;
    }
  }

  /// $params[0]: pool id
  /// $params[1]: post id
  public function addPostInPool($poolId, $postId) {
    if(!Plugins::callFunction('users_plugin', 'isConnected')) {
      return false;
    }

    // Get pool
    $pool = $this->getPool(array($poolId));
    if(!$pool) {
      return false;
    }

    $db = $this->getInstance();

    $req = $db->prepare('INSERT INTO pool_post (post_id, pool_id) VALUES (:post_id, :pool_id)');
    $ret = $req->execute(array('post_id' => $postId, 'pool_id' => $poolId));

    return $ret;
  }

  /// $params[0]: pool id
  /// $params[1]: post id
  public function removePostFromPool($poolId, $postId) {
    if(!Plugins::callFunction('users_plugin', 'isConnected')) {
      return false;
    }

    // Get pool
    $pool = $this->getPool(array($poolId));
    if(!$pool) {
      return false;
    }

    $db = $this->getInstance();

    $req = $db->prepare('DELETE FROM pool_post WHERE post_id=:post_id AND pool_id=:pool_id');
    $ret = $req->execute(array('post_id' => $postId, 'pool_id' => $poolId));

    return $ret;
  }

  /// $params[0]: pool id
  /// $params[1]: post id
  public function postIsInPool($poolId, $postId) {
    if(!Plugins::callFunction('users_plugin', 'isConnected')) {
      return false;
    }

    // Get pool
    $pool = $this->getPool(array($poolId));
    if(!$pool) {
      return false;
    }

    $db = $this->getInstance();

    $req = $db->prepare('SELECT * FROM pool_post WHERE pool_id=:pool_id AND post_id=:post_id');
    $ret = $req->execute(array('post_id' => $postId, 'pool_id' => $poolId));

    if(false == $ret) {
      return false;
    }

    if($req->rowCount() < 1) {
      return false;
    }

    return true;
  }
}

?>
