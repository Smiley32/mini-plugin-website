<?php


class Export_database_plugin {
  private static $_instance = null;

  private static function getInstance() {
    if(!isset(self::$_instance)) {
      $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
      self::$_instance = new PDO('mysql:host=localhost;dbname=website', 'root', '', $pdo_options);
    }
    return self::$_instance;
  }

  /// $params[0]: category
  /// $params[1]: tag
  public static function addTagInCategory($params) {
    $category = $params[0];
    $tag = mb_strtolower($params[1]);

    $db = self::getInstance();

    $req = $db->prepare('SELECT * FROM tag_category WHERE category=:category');
    $ret = $req->execute(array('category' => $category));

    if(!$ret) {
      return false;
    }

    $fetched = $req->fetch();
    $categoryId = $fetched['id'];

    $req = $db->prepare('SELECT * FROM tags WHERE tag=:tag');
    $ret = $req->execute(array('tag' => $tag));
    if(!$ret || $req->rowCount() > 0) {
      return false;
    }

    $req = $db->prepare('INSERT INTO tags (tag, category_id) VALUES (:tag, :category_id)');
    $ret = $req->execute(array('tag' => $tag, 'category_id' => $categoryId));

    if(!$ret) {
      return false;
    }

    return true;
  }

  public static function getPostsCount($params) {
    $db = self::getInstance();
    $req = $db->prepare('SELECT COUNT(1) AS count FROM posts');
    $ret = $req->execute();

    if(!$ret) {
      return false;
    }

    return $req->fetch();
  }

  public static function getCategories($params) {
    $db = self::getInstance();

    $req = $db->prepare('SELECT * FROM tag_category ORDER BY category');
    $ret = $req->execute();

    return !$ret ? false : $req->fetchAll();
  }

  public static function getTagsInCategoryByPost($params) {
    $category = $params[0];
    $post = $params[1];

    $db = self::getInstance();
    $req = $db->prepare(<<<SQL
SELECT
  tags.*
FROM
  tags,
  tag_category,
  post_tag
WHERE
      tags.category_id=tag_category.id
  AND tag_category.category=:category
  AND post_tag.post_id=:post
  AND post_tag.tag_id=tags.id
ORDER BY tags.tag
SQL
);
    $ret = $req->execute(array('category' => $category,
                               'post'     => $post));

    return !$ret ? false : $req->fetchAll();
  }

  public static function searchTagInCategory($params) {
    $category = $params[0];
    $tag = '%' . $params[1] . '%';

    $db = self::getInstance();
    $req = $db->prepare(<<<SQL
SELECT
  tags.*
FROM
  tags,
  tag_category
WHERE
      tags.category_id=tag_category.id
  AND tag_category.category=:category
  AND tags.tag LIKE :tag
ORDER BY tags.tag
SQL
);
    $ret = $req->execute(array('category' => $category,
                               'tag' => $tag));

    return !$ret ? false : $req->fetchAll();
  }

  private static function createDatabase() {
    $sql = <<<SQL
CREATE TABLE users (
  id        INT AUTO_INCREMENT,
  pseudo    VARCHAR(50)   NOT NULL,
  avatar    VARCHAR(32),
  favorites INT,
  password  VARCHAR(255)  NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (favorites) REFERENCES pools(id)
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
  description TEXT        DEFAULT     '',
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
  }
}

?>
