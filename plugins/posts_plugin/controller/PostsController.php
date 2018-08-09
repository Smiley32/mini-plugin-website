<?php

class PostsController extends Controller {

  protected function action_add() {
    $this->addScript('add.js');
    $this->addStyle('post.css');

    $this->setTitle('{>add_title<}');

    if(isset($_GET['submit'])) {
      $file = $this->checkGet('file');

      $tags = 'tagme';
      /*
      if(!isset($_GET['tags']) || '' == $_GET['tags']) {
        $tags = 'tagme';
      } else {
        $tags = $_GET['tags'];
      }
      */

      if(!$this->data['error']) {
        $ret = Plugins::callFunction('database_plugin', 'addPost', $file, $tags);
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
    $this->addStyle('post.css');
    $this->addScript('show.js');

    $id = $this->checkGet('id');

    $this->setTitle('{>post<} ' . $id);

    if(!$this->data['error'] && isset($_POST['submit'], $_POST['tags']) && '' != $_POST['tags']) {
      $tags = $_POST['tags'];

      $ret = Plugins::callFunction('database_plugin', 'updatePostTags', $id, $tags);

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

    $post = Plugins::callFunction('database_plugin', 'getPost', $id);

    $this->data['isImage'] = false;

    if(!$post) {
      $this->addError('{>error_database<}');
      return;
    }

    $this->data['isImage'] = true;
    $this->data['path'] = '/data/posts/' . $post['hash'];
    $this->data['postId'] = $id;

    $this->data['isConnected'] = Plugins::callFunction('users_plugin', 'isConnected');

    // Smimilar images
    $similars = Plugins::callFunction('database_plugin', 'getSimilarPosts', $id);

    if(!$similars) {
      $this->addError('{>error_similars<}');
    } else {
      $this->data['similars'] = $similars;
    }

    // Similar colors
    $colors = Plugins::callFunction('database_plugin', 'getSameMainColorsPosts', $id);

    if(!$colors) {
      $this->data['colors'] = array();
    } else {
      $this->data['colors'] = $colors;
    }

    // Similar tags
    $similarTags = Plugins::callFunction('database_plugin', 'getSimilarTagsPosts', $id);

    if(!$similarTags) {
      $this->data['similarTagsPosts'] = array();
    } else {
      $this->data['similarTagsPosts'] = $similarTags;
    }
  }

  protected function action_upload() {
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
