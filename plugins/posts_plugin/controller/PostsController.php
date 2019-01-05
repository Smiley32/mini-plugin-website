<?php

class PostsController extends Controller {

  protected function action_add() {
    $this->addScript('add.js');
    $this->addStyle('post.css');

    $this->setTitle('{>add_title<}');

    if(isset($_GET['submit'])) {
      $file = $this->checkGet('file');

      $tags = 'tagme';

      if(!$this->data['error']) {
        $ret = $this->getModel()->addPost($file, $tags);
        if(true !== $ret) {
          switch($ret) {
            case 1:
              $this->addError('{>error_not_connected<}');
              break;
            case 2:
              $this->addError('{>error_file_doesnt_exists<}');
              break;
            case 3:
              $this->addError('{>error_file_exists<}');
              break;
            case 4:
              $this->addError('{>error_file_extension<}');
              break;
            default:
              $this->addError('{>error_database<}');
              break;
          }
        }
      }
    }
  }

  protected function action_show() {
    $id = $this->checkGet('id');

    if($id === false) {
      return;
    }

    $this->setTitle('{>post<} ' . $id);

    $model = $this->getModel();

    if(isset($_POST['submitComment']) && isset($_POST['comment'])) {
      $comment = trim($_POST['comment']);
      if($comment != '') {
        $ret = $model->addComment($id, $comment);
      }
    }

    if(!$this->data['error'] && isset($_POST['submit'], $_POST['tags']) && '' != $_POST['tags']) {
      $tags = $_POST['tags'];

      $ret = $model->updatePostTags($id, $tags);

      if(true !== $ret) {
        switch($ret) {
          case 1:
            $this->addError('{>error_no_tags<}');
            break;
          case 2:
            $this->addError('{>error_database<}');
            break;
          case 3:
            $this->addError('{>error_not_connected_tags<}');
            break;
          default:
            $this->addError('{>error_database<}');
            break;
        }
        return;
      }
    }

    $post = $model->getPost($id);

    $this->data['isImage'] = false;
    $this->data['isVideo'] = false;

    switch($post['ext']) {
      case 'jpg':
      case 'jpeg':
      case 'png':
      case 'gif':
        $this->data['isImage'] = true;
        break;

      case 'mp4':
      case 'mkv':
      case 'avi':
      case 'mov':
      case 'wmv':
      case 'flv':
        $this->data['isVideo'] = true;
        $this->data['mime'] = $this->getMinme($post['ext']);
        break;
    }

    if(!$post) {
      $this->addError('{>error_database<}');
      return;
    }

    $this->data['path'] = '[[data/posts]]/' . $post['hash'];
    $this->data['thumbnailPath'] = '[[data/thumbnails]]/' . $post['hash'];
    $this->data['postId'] = $id;

    $this->data['description'] = $post['description'];
    $this->data['uploader'] = $post['uploader'];
    $this->data['date'] = $post['upload_date'];
    $this->data['hash'] = $post['hash'];

    $size = $post['size'];
    $unit = 'o';
    if($size >= 1024) {
      $size /= 1024;
      $unit = 'kio';
    }
    if($size >= 1024) {
      $size /= 1024;
      $unit = 'mio';
    }
    if($size >= 1024) {
      $size /= 1024;
      $unit = 'gio';
    }
    $this->data['size'] = number_format($size, 2) . ' ' . $unit;

    $this->data['dimensions'] = $post['width'] . ' x ' . $post['height'];
    $this->data['extension'] = $post['ext'];
    $this->data['score'] = $post['score'];

    switch($post['rating']) {
      case 1:
        $this->data['rating'] = '{>safe_rating<}';
        break;
      case 2:
        $this->data['rating'] = '{>questionable_rating<}';
        break;
      case 3:
        $this->data['rating'] = '{>explicit_rating<}';
        break;
      default:
        $this->data['rating'] = '{>unknown_rating<}';
        break;
    }

    $this->data['tags'] = $model->getTags($id);

    $this->data['isConnected'] = Plugins::callFunction('users_plugin', 'isConnected');

    // Comments
    $comments = $model->getComments($id);

    if(!$comments) {
      $this->data['comments'] = array();
    } else {
      $this->data['comments'] = $comments;
    }

    if($this->data['isImage']) {
      // Smimilar images
      $similars = $model->getSimilarPosts($id);

      if(!$similars) {
        $this->addError('{>error_similars<}');
      } else {
        $this->data['similars'] = $similars;
      }

      // Similar colors
      $colors = $model->getSameMainColorsPosts($id);

      if(!$colors) {
        $this->data['colors'] = array();
      } else {
        $this->data['colors'] = $colors;
      }
    }

    // Similar tags
    $similarTags = $model->getSimilarTagsPosts($id);

    if(!$similarTags) {
      $this->data['similarTagsPosts'] = array();
    } else {
      $this->data['similarTagsPosts'] = $similarTags;
    }

    // Links
    $links = $model->getLinks($id);

    if(!$links) {
      $this->data['links'] = array();
    } else {
      $this->data['links'] = $links;
    }

    if($this->data['isVideo']) {
      $this->addStyle('video-js.min.css');
      $this->addScript('video.min.js');
    }

    $this->addStyle('post.css');
    $this->addScript('show.js');

    $model->increaseScore($id);
  }

  private function _decode($chunk) {
    $chunk = explode(';base64,', $chunk);

    if(!is_array($chunk) || !isset($chunk[1])) {
      return false;
    }

    $chunk = base64_decode($chunk[1]);
    if(!$chunk) {
      echo 'là';
      return false;
    }

    return $chunk;
  }

  protected function action_upload() {
    $this->setAjax(true);

    if(isset($_GET['u']) && $_GET['u'] == '1') {
      $json = json_decode(stripslashes(file_get_contents('php://input')), true);

      if($json['action'] == 'upload') {
        if(isset($json['id']) && $json['id'] !== false) {
          $id = $json['id'];
        } else {
          $id = uniqid();
        }

        $ext = $this->getExtension($json['file_type']);

        $filePath = "uploads/$id.$ext";

        $chunk = $this->_decode($json['file_data']);

        if($chunk === false) {
          // error
          $this->_reserved['body'] = '{"error": 1}';
        } else {
          file_put_contents($filePath, $chunk, FILE_APPEND);

          // success, return id
          $this->_reserved['body'] = '{"error": 0, "id": "' . $id . '"}';
        }
      }
    }
  }

  protected function action_uploadOLD() {
    $this->setAjax(true);

    /*/ // May be usefull one day
      $fileName = $_POST['name1'];
      $exploded = explode('.', $fileName);
      $ext = end($exploded);
      error_log('extension: ' . $ext);
    /*/

    $data = $_POST['file'];
    // Split the data into two. Data format is "data:<MIME info>;base64,<base64 encoded string>"
    $data = explode(',', $data);
    $data[1] = str_replace(' ', '+', $data[1]);

    $ext = $this->getExtension(substr(substr($data[0], 5), 0, -7));
    $id = uniqid();

    $file = fopen("uploads/$id.$ext", 'w');
    fwrite($file, base64_decode($data[1])); // decode and write to file
    fclose($file);

    $this->_reserved['body'] = '{"error": 0, "file": "' . $id . '.' . $ext . '"}';
  }

  private function getMinme($ext) {
    $mimes = array(
      'pdf' => 'application/pdf',
      'zip' => 'application/zip',
      'rar' => 'application/x-rar',
      'midi' => 'audio/midi',
      'mp3' => 'audio/mp3',
      'wav' => 'audio/wav',
      'bmp' => 'image/bmp',
      'gif' => 'image/gif',
      'jpg' => 'image/jpeg',
      'png' => 'image/png',
      'txt' => 'text/plain',
      'avi' => 'video/avi',
      'mp4' => 'video/mp4',
      'flv' => 'video/x-flv',
      'webm' => 'video/webm',
      'wmv' => 'video/x-ms-wmv',
      'flac' => 'audio/x-flac',
      'ogg' => 'audio/ogg',
      '7z' => 'application/x-7z-compressed',
      'ico' => 'image/x-ico'
    );

    return $mimes[mb_strtolower($ext)];
  }

  private function getExtension($mime) {
    $extensions = array(
      'application/pdf'             => 'pdf',
      'application/force-download'  => 'pdf',
      'application/x-download'      => 'pdf',
      'binary/octet-stream'         => 'pdf',
      'application/x-zip'           => 'zip',
      'application/zip'             => 'zip',
      'application/x-zip-compressed'=> 'zip',
      'application/s-compressed'    => 'zip',
      'multipart/x-zip'             => 'zip',
      'application/x-rar'           => 'rar',
      'application/rar'             => 'rar',
      'application/x-rar-compressed'=> 'rar',
      'audio/midi'                  => 'midi',
      'audio/mpeg'                  => 'mp3',
      'audio/mpg'                   => 'mp3',
      'audio/mpeg3'                 => 'mp3',
      'audio/mp3'                   => 'mp3',
      'audio/x-wav'                 => 'wav',
      'audio/wave'                  => 'wav',
      'audio/wav'                   => 'wav',
      'image/bmp'                   => 'bmp',
      'image/x-bmp'                 => 'bmp',
      'image/x-bitmap'              => 'bmp',
      'image/x-xbitmap'             => 'bmp',
      'image/x-win-bitmap'          => 'bmp',
      'image/x-windows-bmp'         => 'bmp',
      'image/ms-bmp'                => 'bmp',
      'image/x-ms-bmp'              => 'bmp',
      'application/bmp'             => 'bmp',
      'application/x-bmp'           => 'bmp',
      'application/x-win-bitmap'    => 'bmp',
      'image/gif'                   => 'gif',
      'image/jpeg'                  => 'jpg',
      'image/pjpeg'                 => 'jpg',
      'image/png'                   => 'png',
      'image/x-png'                 => 'png',
      'text/plain'                  => 'txt',
      'video/x-msvideo'             => 'avi',
      'video/msvideo'               => 'avi',
      'video/avi'                   => 'avi',
      'application/x-troff-msvideo' => 'avi',
      'video/mp4'                   => 'mp4',
      'video/x-flv'                 => 'flv',
      'video/webm'                  => 'webm',
      'video/x-ms-wmv'              => 'wmv',
      'video/x-ms-asf'              => 'wmv',
      'audio/x-flac'                => 'flac',
      'audio/ogg'                   => 'ogg',
      'video/ogg'                   => 'ogg',
      'application/ogg'             => 'ogg',
      'application/x-7z-compressed' => '7z',
      'application/x-compressed'    => '7z',
      'application/x-zip-compressed'=> '7z',
      'image/x-icon'                => 'ico',
      'image/x-ico'                 => 'ico',
      'image/vnd.microsoft.icon'    => 'ico'
    );

    return $extensions[mb_strtolower($mime)];
  }
}

?>
