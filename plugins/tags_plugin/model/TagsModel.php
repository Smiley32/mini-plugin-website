<?php

class TagsModel extends Database {

  public function removeTagFromPost($post, $tag) {
    $tag = mb_strtolower($tag);
    $post = (int)$post;

    if(!Plugins::callFunction('users_plugin', 'isConnected')) {
      return false;
    }

    $db = $this->getInstance();

    $req = $db->prepare('SELECT * FROM tags WHERE tag=:tag');
    $ret = $req->execute(array('tag' => $tag));

    if($ret == false) {
      return false;
    }

    $fetched = $req->fetch();
    $id = $fetched['id'];

    $req = $db->prepare('DELETE FROM post_tag WHERE post_id=:post AND tag_id=:tag');
    $ret = $req->execute(array(
      'post' => $post,
      'tag' => $id
    ));

    if(!$ret) {
      return false;
    }

    return true;
  }

  public function addTagInCategory($category, $tag) {
    $tag = mb_strtolower($tag);

    $db = $this->getInstance();

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

  public function getTagsInCategoryByPost($category, $post) {
    $db = $this->getInstance();
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

  public function searchTagInCategory($category, $tag) {
    $tag = '%' . $tag . '%';

    $db = $this->getInstance();
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

  public function getCategories() {
    $db = $this->getInstance();

    $req = $db->prepare('SELECT * FROM tag_category ORDER BY category');
    $ret = $req->execute();

    return !$ret ? false : $req->fetchAll();
  }
}

?>
