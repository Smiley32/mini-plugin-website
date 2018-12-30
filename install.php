<?php
/// WARNING!! This file must be deleted after an installation

require_once('core/Database.php');

class InstallDatabase extends Database {
  public function getDatabase() {
    return $this->getInstance();
  }
}

$class = new InstallDatabase();
$db = $class->getDatabase();

$sql = <<<SQL
CREATE TABLE users (
  id        INT AUTO_INCREMENT,
  pseudo    VARCHAR(50)   NOT NULL,
  avatar    VARCHAR(32),
  favorites INT,
  password  VARCHAR(255)  NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE file_types (
  id INT AUTO_INCREMENT,
  name        VARCHAR(50) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE file_ext (
  id          INT         AUTO_INCREMENT,
  ext         VARCHAR(10) NOT NULL,
  type_id     INT         NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (type_id) REFERENCES file_types(id)
);

CREATE TABLE posts (
  id          INT         AUTO_INCREMENT,
  hash        VARCHAR(32) NOT NULL,
  type_ext_id INT         NOT NULL,
  size        INT         NOT NULL,
  width       INT,
  height      INT,
  upload_date DATE        NOT NULL,
  rating      INT         NOT NULL,
  uploader_id INT         NOT NULL,
  perceptual_hash BIGINT  NOT NULL,
  description TEXT,
  PRIMARY KEY (id),
  FOREIGN KEY (type_ext_id) REFERENCES file_ext(id),
  FOREIGN KEY (uploader_id) REFERENCES users(id)
);

CREATE TABLE post_colors (
  post_id     INT         NOT NULL,
  color       INT         NOT NULL,
  PRIMARY KEY (post_id, color),
  FOREIGN KEY (post_id) REFERENCES posts(id)
);

CREATE TABLE tag_category (
  id          INT         AUTO_INCREMENT,
  category    VARCHAR(50) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE tags (
  id          INT         AUTO_INCREMENT,
  tag         VARCHAR(50) NOT NULL,
  category_id INT         NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (category_id) REFERENCES tag_category(id)
);

CREATE TABLE post_tag (
  post_id     INT         NOT NULL,
  tag_id      INT         NOT NULL,
  PRIMARY KEY (post_id, tag_id),
  FOREIGN KEY (post_id)   REFERENCES posts(id),
  FOREIGN KEY (tag_id)    REFERENCES tags(id)
);

CREATE TABLE favorites (
  user_id     INT         NOT NULL,
  post_id     INT         NOT NULL,
  PRIMARY KEY (user_id, post_id),
  FOREIGN KEY (user_id)   REFERENCES users(id),
  FOREIGN KEY (post_id)   REFERENCES posts(id)
);

CREATE TABLE pools (
  id          INT         AUTO_INCREMENT,
  creator     INT         NOT NULL,
  title       VARCHAR(50) NOT NULL,
  description VARCHAR(255),
  rating      INT         NOT NULL,
  private     BOOLEAN     NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (creator)   REFERENCES users(id)
);

CREATE TABLE pool_post (
  post_id     INT         NOT NULL,
  pool_id     INT         NOT NULL,
  PRIMARY KEY (post_id, pool_id),
  FOREIGN KEY (post_id)   REFERENCES posts(id),
  FOREIGN KEY (pool_id)   REFERENCES pools(id)
);

CREATE TABLE comments (
  id          INT         AUTO_INCREMENT,
  post_id     INT         NOT NULL,
  date_added  DATETIME    DEFAULT    CURRENT_TIMESTAMP,
  user_id     INT         NOT NULL,
  content     TEXT        NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (post_id)   REFERENCES posts(id),
  FOREIGN KEY (user_id)   REFERENCES users(id)
);
SQL;

$req = $db->prepare($sql);
$ret = $req->execute();

if(!$ret) {
  echo 'An error has occured 1';
  exit(1);
}

$req = $db->prepare('INSERT INTO tag_category (category) VALUES (:category)');
$ret = $req->execute(array('category' => 'default'));

if(!$ret) {
  echo 'An error has occured 2';
  exit(1);
}

$req = $db->prepare('INSERT INTO tags (tag, category_id) VALUES (:tag, 1)');
$ret = $req->execute(array('tag' => 'tagme'));

if(!$ret) {
  echo 'An error has occured 3';
  exit(1);
}

$req = $db->prepare('INSERT INTO file_types (name) VALUES (:name)');
$ret = $req->execute(array('name' => 'image'));

if(!$ret) {
  echo 'An error has occured 4';
  exit(1);
}

$id = $db->lastInsertId();

$req = $db->prepare('INSERT INTO file_ext (ext, type_id) VALUES (\'jpg\', :id), (\'jpeg\', :id), (\'png\', :id), (\'gif\', :id)');
$ret = $req->execute(array('id' => $id));

if(!$ret) {
  echo 'An error has occured 5';
  exit(1);
}

echo 'success!';
