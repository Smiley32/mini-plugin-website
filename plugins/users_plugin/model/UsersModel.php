<?php

class UsersModel extends Database {
  /**
   * Add an user in the database
   *
   * @param $pseudo
   * @param $password The password, not hashed
   * @return true if no error, the error number else
   * -1 -> Existing user
   * -2 -> Request error
   */
  public function addUser($pseudo, $password) {
    $db = $this->getInstance();

    // Existence verification
    $req = $db->prepare('SELECT pseudo FROM users WHERE pseudo=:pseudo');
    $req->execute(array('pseudo' => $pseudo));

    if($req->rowCount() != 0) {
      return -1; // Existing user
    }

    // Password hash
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $req = $db->prepare('INSERT INTO users (pseudo, password, favorites) VALUES (:pseudo, :password, :favorites)');
    $ret = $req->execute(array(
      'pseudo' => $pseudo,
      'password' => $hash,
      'favorites' => NULL
    ));

    if(!$ret) {
      return -2;
    }

    $insertedUser = $db->lastInsertId();

    $model = Plugins::getModel('posts_search_plugin', 'posts');

    // Create a private pool for the favorites
    $pool = $model->createPool(
      $insertedUser,
      'Favorites',
      'My favorites',
      0,
      true
    );

    if(false === $pool) {
      return -3;
    }

    $req = $db->prepare('UPDATE users SET favorites=:poolId WHERE id=:userId');
    $ret = $req->execute(array('poolId' => $pool, 'userId' => $insertedUser));

    if(!$ret) {
      return -4;
    }

    return $insertedUser;
  }

  /**
   * Get an user from the database with a pseudo and a password
   *
   * @param string $pseudo The pseudo to find
   * @param string $password The password, musn't be hashed
   * @return
   *    success:  an user
   *    error:    false
   */
  public function getUser($pseudo, $password) {
    $db = $this->getInstance();

    $req = $db->prepare('SELECT * FROM users WHERE pseudo=:pseudo');
    $ret = $req->execute(array('pseudo' => $pseudo));

    if(!$ret) {
      return false;
    }

    $user = $req->fetch();
    if(password_verify($password, $user['password'])) {
      return $user;
    } else {
      return false;
    }
  }
}

?>
