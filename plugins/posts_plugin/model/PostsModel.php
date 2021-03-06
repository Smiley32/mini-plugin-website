<?php

class PostsModel extends Database {

  public function increaseScore($id) {
    $id = (int)$id;

    $db = $this->getInstance();

    $req = $db->prepare('UPDATE posts SET score=score+1 WHERE id=:id');
    $ret = $req->execute(array('id' => $id));

    if(!$ret) {
      return false;
    }

    return true;
  }

  public function getLinks($src) {
    $src = (int)$src;

    $db = $this->getInstance();

    $req = $db->prepare('SELECT posts.id, posts.hash, users.pseudo, posts.width, posts.height FROM links, posts, users WHERE posts.uploader_id=users.id AND links.dest=posts.id AND links.src=:src');
    $ret = $req->execute(array('src' => $src));

    if(!$ret) {
      return false;
    }

    return $req->fetchAll();
  }

  /**
   * Add a comment on a post
   * An user must be connected to post a comment.
   *
   * @param int $postId : Post to comment
   * @param string $comment : content of the comment
   * @return
   *      true  -> no error
   *      1     -> no user connected
   *      2     -> database error
   */
  public function addComment($postId, $comment) {
    $db = $this->getInstance();

    $comment = htmlspecialchars($comment);
    $postId = (int)$postId;

    $user = Plugins::callFunction('users_plugin', 'getCurrentUser');
    if(!$user) {
      return 1;
    }

    $req = $db->prepare('INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)');
    $ret = $req->execute(array(
      'post_id' => $postId,
      'user_id' => $user['id'],
      'content' => $comment
    ));

    if(!$ret) {
      return 2;
    }

    return true;
  }

  /**
   * Get the comments for a given post
   *
   * @param int $postId   : Post id
   * @param bool $asc     : Ascending sorting (most recent first)
   * @return mixed
   *      false -> error
   *      fetched comments else
   */
  public function getComments($postId, $asc = false) {
    $postId = (int)$postId;

    $db = $this->getInstance();

    $sort = 'ASC';
    if(!$asc) {
      $sort = 'DESC';
    }

    $req = $db->prepare('SELECT comments.*, users.pseudo AS user FROM comments, users WHERE comments.user_id=users.id AND comments.post_id=:post_id ORDER BY date_added ' . $sort);
    $ret = $req->execute(array('post_id' => $postId));

    return !$ret ? false : $req->fetchAll();
  }

  public function getPost($id) {
    $db = $this->getInstance();

    $req = $db->prepare('SELECT posts.*, file_ext.ext, users.pseudo AS uploader FROM posts, file_ext, users WHERE posts.type_ext_id=file_ext.id AND posts.id=:id AND posts.uploader_id=users.id');
    $ret = $req->execute(array('id' => $id));

    return !$ret ? false : $req->fetch();
  }

  /// $params[0]: post id
  public function getSimilarTagsPosts($id) {
    $db = $this->getInstance();

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

  public function getSameMainColorsPosts($id) {
    $db = $this->getInstance();

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

  public function getSimilarPosts($id) {
    $db = $this->getInstance();

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

  public function regenThumb($postId) {
    $postId = (int)$postId;

    $post = $this->getPost($postId);

    $ext = $post['ext'];
    $path = 'data/posts/' . $post['hash'];

    $isPicture = ($ext === 'jpg') || ($ext === 'png') || ($ext === 'bmp') || ($ext === 'gif');

    if($isPicture) {
      list($width, $height) = getimagesize($path);
    } else {
      $dim = Plugins::callFunction('video_plugin', 'getSize', $path);
      $width = $dim['width'];
      $height = $dim['height'];
    }

    // remove old thumbnail
    unlink('data/thumbnails/' . $post['hash']);

    // Create thumbnail
    if($isPicture) {
      $ret = Plugins::callFunction('image_plugin', 'createThumbnail', $path, 'data/thumbnails/' . $post['hash']);
      if(!$ret) {
        return 6;
      }
    } else {
      // if video
      $ret = Plugins::callFunction('video_plugin', 'createThumbnail', $path, 'data/thumbnails/' . $post['hash']);
      if(!$ret) {
        return 6;
      }
    }

    return 0;
  }

  /// $params[0]: fileName (in uploads/)
  /// $params[1]: $tags // Always tagme yet
  /// Errors:
  /// 1 -> not connected
  /// 2 -> file doesn't exist
  /// 3 -> Same file is already present
  /// 4 -> Unknown file extension
  /// 5 -> An error has occured
  /// 6 -> Error while creating the thumbnail
  public function addPost($fileName, $tags) {
    $user = Plugins::callFunction('users_plugin', 'getCurrentUser');
    if(false == $user) {
      return 1;
    }

    $files = glob('uploads/' . $fileName . '*');

    if(!isset($files[0])) {
      return 2;
    }
    $fileName = $files[0];

    $path = $fileName;

    $ext = pathinfo($fileName, PATHINFO_EXTENSION);

    $size = filesize($path);

    $hash = md5(file_get_contents($path, false, NULL, 0, 10000));

    if(file_exists('data/posts/' . $hash)) {
      return 3;
    }

    $width = 0;
    $height = 0;

    $isPicture = false;

    if(($ext === 'jpg') || ($ext === 'png') || ($ext === 'bmp') || ($ext === 'gif')) {
      $isPicture = true;
      list($width, $height) = getimagesize($path);
    } else {
      $dim = Plugins::callFunction('video_plugin', 'getSize', $path);
      $width = $dim['width'];
      $height = $dim['height'];
    }

    $db = $this->getInstance();

    $req = $db->prepare('SELECT * FROM file_ext WHERE ext=:ext');
    $ret = $req->execute(array('ext' => $ext));

    if(!$ret) {
      return 4;
    }

    if($isPicture === true) {
      // Compute perceptual hash
      $perceptualHash = Plugins::callFunction('image_plugin', 'createPerceptualHash', $path);
    } else {
      $perceptualHash = "0";
    }

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
    if($isPicture) {
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
    } else {
      // if video
      $ret = Plugins::callFunction('video_plugin', 'createThumbnail', $path, 'data/thumbnails/' . $hash);
      if(!$ret) {
        return 6;
      }
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

  public function getTags($postId) {
    $postId = (int)$postId;

    $db = $this->getInstance();

    $req = $db->prepare('SELECT * FROM tags, post_tag WHERE tags.id=post_tag.tag_id AND post_tag.post_id=:post_id');
    $ret = $req->execute(array('post_id' => $postId));

    if(!$ret) {
      return false;
    }

    return $req->fetchAll();
  }

  /// $params[0]: post id
  /// $params[1]: new tags
  /// 1: no tags
  /// 2: database error
  /// 3: not connected
  public function updatePostTags($postId, $tags) {
    $tags = trim($tags);

    if(!Plugins::callFunction('users_plugin', 'isConnected')) {
      return 3;
    }

    if('' == $tags) {
      return 1;
    }

    $db = $this->getInstance();

    $tagArray = explode(';', $tags);

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
}

?>
