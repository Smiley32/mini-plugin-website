<?php

class Tree {
  private $_value = null;

  private $_childs = null;

  public function __construct($value, $evaluate = false) {
    if(empty($value)) {
      // ...
      return;
    }

    if(!$evaluate) {
      $this->_value = $value;
    } else {
      $this->eval($value);
    }
  }

  /// return a Tree
  private function eval($expr) {
    $expr = trim($expr, ' ');

    if('(' == $expr[0]) {
      $position = -1;
      $length = strlen($expr);
      for($i = $length - 1; $i > 0; $i--) {
        if(')' == $expr[$i]) {
          $position = $i;
          break;
        }
      }

      if(-1 != $position) {
        if($position == $length - 1) {
          $expr = substr($expr, 1);
          $expr = substr($expr, 0, -1);
          $this->eval($expr);
        } else {
          $left = trim(substr($expr, 1, $position - 1), ' ');
          $right = trim(substr($expr, $position + 1), ' ');

          $lTree = new Tree($left, true);
          $rTree = new Tree(substr($right, 1), true);

          $operator = $right[0];
          $this->_value = $operator;
          $this->addChild($lTree);
          $this->addChild($rTree);
        }
      } else {
        // Error missing parenthesis
        return eval(substr($expr, 1));
      }

      // remove the parenthesis and reevaluate the expr
      // $this->eval(trim(trim($expr, '('), ')'));
    } else {
      $length = strlen($expr);
      $separator = '';
      $position = -1;
      for($i = 0; $i < $length; $i++) {
        if(';' == $expr[$i] || '|' == $expr[$i]) {
          $separator = $expr[$i];
          $position = $i;
          break;
        }
      }

      if(-1 == $position) {
        // There is only one tag
        if('-' == $expr[0] || '!' == $expr[0]) {
          $this->_value = $expr[0];
          $newTree = new Tree(substr($expr, 1), false);
          $this->addChild($newTree);
        } else {
          $this->_value = $expr;
        }
      } else {
        $this->_value = $separator;
        $left = new Tree(substr($expr, 0, $position), true);
        $right = new Tree(substr($expr, $position + 1), true);
        $this->addChild($left);
        $this->addChild($right);
      }
    }
  }

  public function addChild($tree) {
    if(null == $this->_childs) {
      $this->_childs = array();
    }

    $this->_childs[] = $tree;
  }

  public function isLeaf() {
    return null == $this->_childs;
  }

  public function toString() {
    error_log('toString: <' . $this->_value . '>');
    if('-' == $this->_value || '!' == $this->_value) {
      return $this->_value . $this->_childs[0]->toString();
    }

    if(';' == $this->_value || '|' == $this->_value) {
      return '(' . $this->_childs[0]->toString() . $this->_value . $this->_childs[1]->toString() . ')';
    }

    return $this->_value;
  }

  public function validate() {
    if('-' == $this->_value || '!' == $this->_value) {
      return $this->_childs[0]->validate();
    }

    if(';' == $this->_value || '|' == $this->_value) {
      return $this->_childs[0]->validate() && $this->_childs[1]->validate();
    }

    $this->_value = preg_replace('/[^a-zA-Z0-9><_ :]/', '', $this->_value);
    return true;
  }

  public function singleRequest($notIn = false) {
    $req = 'posts.id ';

    if($notIn) {
      $req .= 'NOT ';
    }

    $req .= 'IN ( SELECT post_id FROM post_tag, tags WHERE tags.tag=\'' . $this->_value . '\' AND tags.id=post_tag.tag_id ) ';

    return $req;
  }

  public function toSQL() {
    if(null == $this->_value) {
      return '';
    }

    if('-' == $this->_value || '!' == $this->_value) {
      return $this->_childs[0]->singleRequest(true);
    }

    if(';' == $this->_value) {
      return '(' . $this->_childs[0]->toSQL() . ' AND ' . $this->_childs[1]->toSQL() . ')';
    }

    if('|' == $this->_value) {
      return '(' . $this->_childs[0]->toSQL() . ' OR ' . $this->_childs[1]->toSQL() . ')';
    }

    $separator = '';
    $position = false;
    $length = strlen($this->_value);

    for($i = 0; $i < $length; $i++) {
      if(':' == $this->_value[$i] || '>' == $this->_value[$i] || '<' == $this->_value[$i]) {
        $separator = $this->_value[$i];
        $position = $i;
        break;
      }
    }

    if(false === $position) {
      return $this->singleRequest();
    }

    $keyword = substr($this->_value, 0, $position);
    $value = substr($this->_value, $position + 1);

    if(!$keyword || !$value) {
      // Error
      error_log('ERROR');
      exit();
    }

    $sqlSeparator = '=';
    if('<' == $separator) {
      $sqlSeparator = '<';
    } elseif('>' == $separator) {
      $sqlSeparator = '>';
    }

    if('size' == $keyword) {
      return ' posts.size' . $sqlSeparator . $value . ' ';
    } elseif('width' == $keyword) {
      return ' posts.width' . $sqlSeparator . $value . ' ';
    } elseif('height' == $keyword) {
      return ' posts.height' . $sqlSeparator . $value . ' ';
    } elseif('id' == $keyword) {
      return ' posts.id' . $sqlSeparator . $value . ' ';
    } else {
      // Error
      error_log('ERROR: unknown keyword');
      exit();
    }
  }
}

class Export_database_plugin {
  private static $_instance = null;

  private static function getInstance() {
    if(!isset(self::$_instance)) {
      $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
      self::$_instance = new PDO('mysql:host=localhost;dbname=website', 'root', '', $pdo_options);
    }
    return self::$_instance;
  }

  /**
   * Add an user in the database
   *
   * @param $pseudo ($params[0])
   * @param $password ($params[1]) The password, not hashed
   * @return true if no error, the error number else
   * 1 -> Existing user
   * 2 -> Request error
   */
  public static function addUser($params) {
    $pseudo = $params[0];
    $password = $params[1];

    $db = self::getInstance();

    // Existence verification
    $req = $db->prepare('SELECT pseudo FROM users WHERE pseudo=:pseudo');
    $req->execute(array('pseudo' => $pseudo));

    if($req->rowCount() != 0) {
      return 1; // Existing user
    }

    // Create a private pool for the favorites
    $pool = self::createPool(array(
      'creatorId',
      'title',
      'description',
      'rating',
      'private'
    ));

    if(false === $pool) {
      return 2;
    }

    // Password hash
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $req = $db->prepare('INSERT INTO users (pseudo, password, favorites) VALUES (:pseudo, :password, :favorites)');
    $ret = $req->execute(array(
      'pseudo' => $pseudo,
      'password' => $hash,
      'favorites' => $pool
    ));

    if(!$ret) {
      return 2;
    }

    return $db->lastInsertId();
  }

  public static function getUser($params) {
    $pseudo = $params[0];
    $password = $params[1];

    $db = self::getInstance();

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

  /// $params[0]: post id
  /// $params[1]: new tags
  /// 1: no tags
  /// 2: database error
  /// 3: not connected
  public static function updatePostTags($params) {
    $postId = $params[0];
    $tags = trim($params[1]);

    if(!Plugins::callFunction('users_plugin', 'isConnected')) {
      return 3;
    }

    if('' == $tags) {
      return 1;
    }

    $db = self::getInstance();

    $tagArray = explode(' ', $tags);

    $req = $db->prepare('SELECT tags.* FROM tags, post_tag WHERE post_tag.tag_id=tags.id AND post_tag.post_id=:postId');
    $ret = $req->execute(array('postId' => $postId));

    if(!$ret) {
      return 2;
    }

    $fetched = $req->fetchAll();
    $fetchedTags = array();
    foreach($fetched as $tag) {
      $fetchedTags[] = $tag['tag'];
    }

    $notInPost = array_diff($tagArray, $fetchedTags);
    $toDelete = array_diff($fetchedTags, $tagArray);

    // Add tags
    foreach($notInPost as $tag) {
      $req = $db->prepare('SELECT * FROM tags WHERE tag=:tag');
      $ret = $req->execute(array('tag' => $tag));
      if(!$ret || $req->rowCount() == 0) {
        continue;
      }
      $fetched = $req->fetch();

      $req = $db->prepare('INSERT INTO post_tag (post_id, tag_id) VALUES (:postId, :tag)');
      $ret = $req->execute(array('postId' => $postId, 'tag' => $fetched['id']));
    }

    // Delete tags
    foreach($toDelete as $tag) {
      $req = $db->prepare('SELECT * FROM tags WHERE tag=:tag');
      $ret = $req->execute(array('tag' => $tag));
      if(!$ret || $req->rowCount() == 0) {
        continue;
      }
      $fetched = $req->fetch();

      $req = $db->prepare('DELETE FROM post_tag WHERE post_id=:postId AND tag_id=:tag');
      $ret = $req->execute(array('postId' => $postId, 'tag' => $fetched['id']));
    }

    return true;
  }

  /// $params[0]: post id
  public static function getPost($params) {
    $id = $params[0];

    $db = self::getInstance();

/*/
    $req = $db->prepare('SELECT * FROM posts WHERE id<95 AND id>81');
    $ret = $req->execute();

    $posts = $req->fetchAll();
    foreach($posts as $post) {
      // colors
      var_dump('test..');
      $colors = Plugins::callFunction('image_plugin', 'getMainColors', 'data/posts/' . $post['hash']);

      if($colors !== false) {
        foreach($colors as $color) {
          $req = $db->prepare('INSERT INTO post_colors (post_id, color) VALUES (:post_id, :color)');
          $ret = $req->execute(array('post_id' => $post['id'], 'color' => $color));
        }
      }

    }
    exit();
/*/

    $req = $db->prepare('SELECT posts.*, file_ext.ext FROM posts, file_ext WHERE posts.type_ext_id=file_ext.id AND posts.id=:id');
    $ret = $req->execute(array('id' => $id));

    return !$ret ? false : $req->fetch();
  }

  /// $params[0]: post id
  public static function getSimilarTagsPosts($params) {
    $id = $params[0];

    $db = self::getInstance();

    $req = $db->prepare(<<<SQL
SELECT
  p2.*,
  users.pseudo AS uploader,
  COUNT(1) AS sim
FROM
  (SELECT
    tag_id
  FROM
    post_tag
  WHERE
    post_id=:id) AS origin,
  (SELECT
    *
  FROM
    posts,
    post_tag
  WHERE
    post_id=posts.id
    AND posts.id!=:id) AS posts,
  posts AS p2,
  users
WHERE
  origin.tag_id=posts.tag_id
  AND p2.id=posts.id
  AND p2.uploader_id=users.id
GROUP BY(posts.post_id)
ORDER BY sim DESC
LIMIT 0, 6
SQL
);
    $ret = $req->execute(array('id' => $id));

    if(false === $ret) {
      return false;
    }

    if($req->rowCount() == 0) {
      return array();
    }

    return $req->fetchAll();
  }

  /// $params[0]: post id
  public static function getSameMainColorsPosts($params) {
    $id = $params[0];

    $db = self::getInstance();

    $req = $db->prepare('SELECT * FROM posts, post_colors WHERE id=post_id AND id=:id');
    $ret = $req->execute(array('id' => $id));

    if(false === $ret) {
      return false;
    }

    if($req->rowCount() != 3) {
      return array();
    }

    $colors = $req->fetchAll();

    $req = $db->prepare(<<<SQL
SELECT
  posts.*,
  users.pseudo AS uploader
FROM
  posts,
  users
WHERE
  posts.uploader_id=users.id
  AND posts.id IN (
    SELECT
      post_id
    FROM
      post_colors
    WHERE
      color=:color1
      AND post_id!=:id
  )
  AND posts.id IN (
    SELECT
      post_id
    FROM
      post_colors
    WHERE
      color=:color2
      AND post_id!=:id
  )
  AND posts.id IN (
    SELECT
      post_id
    FROM
      post_colors
    WHERE
      color=:color3
      AND post_id!=:id
  )
ORDER BY RAND()
LIMIT 0, 6
SQL
);
    $ret = $req->execute(array( 'color1' => $colors[0]['color'],
                                'color2' => $colors[1]['color'],
                                'color3' => $colors[2]['color'],
                                'id' => $id));

    if(false === $ret) {
      return false;
    }

    if($req->rowCount() == 0) {
      return array();
    }


    return $req->fetchAll();
  }

  /// $params[0]: post id
  public static function getSimilarPosts($params) {
    $id = $params[0];

    $db = self::getInstance();

    $req = $db->prepare('SELECT * FROM posts WHERE id=:id');
    $ret = $req->execute(array('id' => $id));

    if(!$ret) {
      return false;
    }

    $fetched = $req->fetch();
    $perceptualHash = $fetched['perceptual_hash'];

    $req = $db->prepare(<<<SQL
SELECT
  posts.*,
  users.pseudo AS uploader,
  BIT_COUNT(perceptual_hash ^ :perceptual_hash) as hamming_distance
FROM
  posts,
  users
WHERE
  posts.id!=:id
  AND posts.uploader_id=users.id
-- HAVING hamming_distance < 5
ORDER BY hamming_distance
LIMIT 0, 6
SQL
);
    $ret = $req->execute(array('perceptual_hash' => $perceptualHash, 'id' => $id));

    if(!$ret) {
      return false;
    }

    return $req->fetchAll();
  }

  /// $params[0]: tags
  /// $params[1]: page
  public static function getPosts($params) {
    $tags = trim($params[0]);
    $page = $params[1];

    // Construct a tree
    $tree = new Tree($tags, true);
    $tree->validate();
    $str = $tree->toSQL();

    $sql = 'SELECT posts.*, file_ext.ext FROM posts, file_ext WHERE posts.type_ext_id=file_ext.id ';
    if('' != $str) {
      $sql .= 'AND ' . $str;
    }

    $postCount = 24;
    $offset = ($page - 1) * $postCount;

    $sql .= " ORDER BY posts.id DESC LIMIT $offset, $postCount";

    $db = self::getInstance();
    $req = $db->prepare($sql);
    $ret = $req->execute();

    return !$ret ? false : $req->fetchAll();
  }

  /// $params[0]: fileName (in uploads/)
  /// $params[1]: $tags
  /// Errors:
  /// 1 -> not connected
  /// 2 -> file doesn't exist
  /// 3 -> Same file is already present
  /// 4 -> Unknown file extension
  /// 5 -> An error has occured
  /// 6 -> Error while creating the thumbnail
  public static function addPost($params) {
    $user = Plugins::callFunction('users_plugin', 'getCurrentUser');
    if(false == $user) {
      return 1;
    }

    $fileName = $params[0];
    $path = 'uploads/' . $fileName;
    $tags = $params[1];

    if(!file_exists($path)) {
      return 2;
    }

    $ext = pathinfo($fileName, PATHINFO_EXTENSION);

    $size = filesize($path);
    $hash = md5_file($path);

    if(file_exists('data/posts/' . $hash)) {
      return 3;
    }

    $width = null;
    $height = null;

    if(($ext === 'jpg') || ($ext === 'png') || ($ext === 'bmp') || ($ext === 'gif')) {
      list($width, $height) = getimagesize($path);
    }

    $db = self::getInstance();

    $req = $db->prepare('SELECT * FROM file_ext WHERE ext=:ext');
    $ret = $req->execute(array('ext' => $ext));

    if(!$ret) {
      return 4;
    }

    // Compute perceptual hash
    $perceptualHash = Plugins::callFunction('image_plugin', 'createPerceptualHash', $path);

    $fetched = $req->fetch();
    $extId = $fetched['id'];

    $req = $db->prepare(<<<SQL
INSERT INTO posts (
  type_ext_id,
  size,
  width,
  height,
  upload_date,
  rating,
  uploader_id,
  hash,
  perceptual_hash
) VALUES (
  :type_ext_id,
  :size,
  :width,
  :height,
  NOW(),
  0,
  :uploader_id,
  :hash,
  :perceptual_hash
)
SQL
    );
    $ret = $req->execute(array(
      'type_ext_id'   => $extId,
      'size'          => $size,
      'width'         => $width,
      'height'        => $height,
      'uploader_id'   => $user['id'],
      'hash'          => $hash,
      'perceptual_hash' => $perceptualHash
    ));

    if(!$ret) {
      return 5;
    }

    $postId = $db->lastInsertId();

    // colors
    $colors = Plugins::callFunction('image_plugin', 'getMainColors', $path);

    if($colors !== false) {
      foreach($colors as $color) {
        $req = $db->prepare('INSERT INTO post_colors (post_id, color) VALUES (:post_id, :color)');
        $ret = $req->execute(array('post_id' => $postId, 'color' => $color));
      }
    }

    // Add tags
    // Create thumbnail
    $ret = Plugins::callFunction('image_plugin', 'createThumbnail', $path, 'data/thumbnails/' . $hash);
    if(!$ret) {
      return 6;
    }

    // Move file
    rename($path, 'data/posts/' . $hash);

    $tags = trim($tags);
    $tagArray = explode(' ', $tags);

    foreach($tagArray as $tag) {
      $tag = mb_strtolower(trim($tag));
      $req = $db->prepare('SELECT * FROM tags WHERE tag=:tag');
      $ret = $req->execute(array('tag' => $tag));
      if($ret == false || $req->rowCount() == 0) {
        // TODO: handle error
      }
      $fetched = $req->fetch();
      $tagId = $fetched['id'];

      $req = $db->prepare('INSERT INTO post_tag (post_id, tag_id) VALUES (:post_id, :tag_id)');
      $ret = $req->execute(array('post_id'  => $postId,
                                 'tag_id'   => $tagId));
    }


    return true;
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
  favorites INT           NOT NULL,
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

SQL;
  }

  /// $params[0]: creator
  /// $params[1]: title
  /// $params[2]: description
  /// $params[3]: rating
  /// $params[4]: private
  public static function createPool($params) {
    $creatorId    = $params[0];
    $title        = $params[1];
    $description  = $params[2];
    $rating       = $params[3];
    $private      = $params[4];

    $db = self::getInstance();

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
  public static function getPool($params) {
    $id = $params[0];

    $db = self::getInstance();

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
  public static function addPostInPool($params) {
    $poolId = $params[0];
    $postId = $params[1];

    if(!Plugins::callFunction('users_plugin', 'isConnected')) {
      return false;
    }

    // Get pool
    $pool = self::getPool(array($poolId));
    if(!$pool) {
      return false;
    }

    $db = self::getInstance();

    $req = $db->prepare('INSERT INTO pool_post (post_id, pool_id) VALUES (:post_id, :pool_id)');
    $ret = $req->execute(array('post_id' => $postId, 'pool_id' => $poolId));

    return $ret;
  }

  /// $params[0]: pool id
  /// $params[1]: post id
  public static function removePostFromPool($params) {
    $poolId = $params[0];
    $postId = $params[1];

    if(!Plugins::callFunction('users_plugin', 'isConnected')) {
      return false;
    }

    // Get pool
    $pool = self::getPool(array($poolId));
    if(!$pool) {
      return false;
    }

    $db = self::getInstance();

    $req = $db->prepare('DELETE FROM pool_post WHERE post_id=:post_id AND pool_id=:pool_id');
    $ret = $req->execute(array('post_id' => $postId, 'pool_id' => $poolId));

    return $ret;
  }

  /// $params[0]: pool id
  /// $params[1]: post id
  public static function postIsInPool($params) {
    $poolId = $params[0];
    $postId = $params[1];

    if(!Plugins::callFunction('users_plugin', 'isConnected')) {
      return false;
    }

    // Get pool
    $pool = self::getPool(array($poolId));
    if(!$pool) {
      return false;
    }

    $db = self::getInstance();

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

  /// $params[0]: user id
  public static function getFavorites($params) {
    $id = $params[0];

    $db = self::getInstance();

    $req = $db->prepare('SELECT * FROM favorites WHERE user_id=:id ORDER BY post_id DESC');
    $ret = $req->execute(array('id' => $id));

    if(!$ret) {
      return false;
    }
    return $req->fetchAll();
  }

  /// $params[0]: user id
  /// $params[1]: post id
  public static function addFavorite($params) {
    $userId = $params[0];
    $postId = $params[1];

    error_log('--ADD');
    $db = self::getInstance();

    $req = $db->prepare('INSERT INTO favorites (user_id, post_id) VALUES (:user_id, :post_id)');
    $ret = $req->execute(array('user_id' => $userId, 'post_id' => $postId));

    return $ret;
  }

  /// $params[0]: user id
  /// $params[1]: post id
  public static function removeFavorite($params) {
    $userId = $params[0];
    $postId = $params[1];

    error_log('--REMOVE');
    $db = self::getInstance();

    $req = $db->prepare('DELETE FROM favorites WHERE user_id=:user_id AND post_id=:post_id');
    $ret = $req->execute(array('user_id' => $userId, 'post_id' => $postId));

    return $ret;
  }


}

?>
