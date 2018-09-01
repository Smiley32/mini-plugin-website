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

    $ret = ' 1 ';
    switch($keyword) {
      case 'size':
        $ret = ' posts.size' . $sqlSeparator . $value . ' ';
        break;
      case 'width':
        $ret = ' posts.width' . $sqlSeparator . $value . ' ';
        break;
      case 'height':
        $ret = ' posts.height' . $sqlSeparator . $value . ' ';
        break;
      case 'id':
        $ret = ' posts.id' . $sqlSeparator . $value . ' ';
        break;
      case 'ext':
        if(preg_match('/^[a-zA-Z]+$/', $value)) {
          $ret = ' posts.type_ext_id = (SELECT id FROM file_ext WHERE ext' . $sqlSeparator . '\'' . $value . '\')';
        }
        break;
      case 'pool':
        if(preg_match('/^[0-9]+$/', $value)) {
          $user = Plugins::callFunction('users_plugin', 'getCurrentUser');
          if(!$user) {
            $user = array();
            $user['id'] = -1;
          }
          $ret = ' posts.id IN (SELECT pool_post.post_id FROM pool_post, pools WHERE pool_post.pool_id' . $sqlSeparator . $value . ' AND pool_post.pool_id=pools.id AND (pools.creator=' . $user['id'] . ' OR pools.private=0))';
        }
        break;
      default:
        error_log('ERROR: unknown keyword');
    }

    return $ret;
  }
}

class PostsModel extends Database {

  /// $params[0]: tags
  /// $params[1]: page
  public function getPosts($tags, $page) {
    $tags = trim($tags);

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

    $db = $this->getInstance();
    $req = $db->prepare($sql);
    $ret = $req->execute();

    return !$ret ? false : $req->fetchAll();
  }

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
    $poolId = (int)$poolId;
    $postId = (int)$postId;

    if(!Plugins::callFunction('users_plugin', 'isConnected')) {
      return false;
    }

    // Get pool
    $pool = $this->getPool($poolId);
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
    $pool = $this->getPool($poolId);
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
    $pool = $this->getPool($poolId);
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
