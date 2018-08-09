<?php

class Export_image_plugin {

  private static function getMimeType($imagePath) {
    $mime = getimagesize($imagePath);
    $ret = array($mime[0], $mime[1]);

    switch($mime['mime']) {
      case 'image/jpeg':
        $ret[] = 'jpg';
        break;
      case 'image/png':
        $ret[] = 'png';
        break;
      case 'image/bmp':
        $ret[] = 'bmp';
        break;
      default:
        return false;
    }

    return $ret;
  }

  private static function createImage($imagePath) {
    $mime = self::getMimeType($imagePath);

    switch($mime[2]) {
      case 'jpg':
        return imagecreatefromjpeg($imagePath);
        break;
      case 'png':
        return imagecreatefrompng($imagePath);
        break;
      case 'bmp':
        return imagecreatefrombmp($imagePath);
        break;
      default:
        return false;
    }
  }

  private static function create8x8($imagePath) {
    $sourceImage = self::createImage($imagePath);
    if(false === $sourceImage) {
      return false;
    }

    $mime = self::getMimeType($imagePath);
    if(false === $mime) {
      return false;
    }

    // Create an empty 8x8 image
    $newImage = imagecreatetruecolor(8, 8);

    // Resize the source image in the new
    imagecopyresized($newImage, $sourceImage, 0, 0, 0, 0, 8, 8, $mime[0], $mime[1]);

    return $newImage;
  }

  // $params[0]: $imagePath
  // $params[1]: $thumbnailPath
  public static function createThumbnail($params) {
    $imagePath = $params[0];
    $thumbnailPath = $params[1];

    $maxWidth = 300;
    $maxHeight = 300;

    $newImage = self::createSmallerImage($imagePath, 300, 300);
    if(!$newImage) {
      return false;
    }

    imagejpeg($newImage, $thumbnailPath);
    imagedestroy($newImage);

    return true;
  }

  // return new image resource
  private static function createSmallerImage($imagePath, $maxWidth, $maxHeight, &$width = false, &$height = false) {
    $sourceImage = self::createImage($imagePath);
    if(false === $sourceImage) {
      return false;
    }

    $mime = self::getMimeType($imagePath);
    if(false === $mime) {
      return false;
    }

    // Calculate the width and the height of the new image
    $originalWidth = $mime[0];
    $originalHeight = $mime[1];
    $newWidth = 0;
    $newHeight = 0;

    if($originalWidth > $originalHeight) {
      $newWidth = $maxWidth;
      $newHeight = intval($newWidth * $originalHeight / $originalWidth);
    } else {
      $newHeight = $maxHeight;
      $newWidth = intval($newHeight * $originalWidth / $originalHeight);
    }

    if(false !== $width) {
      $width = $newWidth;
    }

    if(false !== $height) {
      $height = $newHeight;
    }

    // Create an empty image
    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // Resize the source image in the new
    imagecopyresized($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

    imagedestroy($sourceImage);

    return $newImage;
  }

  // $params[0]: $hash1
  // $params[1]: $hash2
  // $params[2]: $length
  public static function hammingDistance($params) {
    $hash1 = $params[0];
    $hash2 = $params[1];
    $length = $params[2];

    $count = 0;
    $h1 = $hash1;
    $h2 = $hash2;
    for($i = 0; $i < $length; $i++) {
      if(($h1 & 1) == ($h2 & 1)) {
        $count++;
      }
      $h1 >> 1;
      $h2 >> 1;
    }
    return $count;
  }

  // $params[0]: $imagePath
  public static function getMainColors($params) {
    $imagePath = $params[0];

    $mime = self::getMimeType($imagePath);
    if(false === $mime) {
      return false;
    }

    // Calculate the width and the height of the new image
    $width = $mime[0];
    $height = $mime[1];

    // Create a smaller image to work with
    $image = self::createImage($imagePath);

    if(!$image) {
      return false;
    }

    $palette = array();
    for($i = 0; $i < 64; $i++) {
      $palette[$i] = 0;
    }

    for($x = 0; $x < $width; $x++) {
      for($y = 0; $y < $height; $y++) {
        $rgba = imagecolorat($image, $x, $y);
        $a = ($rgba >> 24) & 0x7F; // transparency, so 127 is transparent
        $r = (($rgba >> 16) & 0xFF) / 255;
        $g = (($rgba >> 8) & 0xFF) / 255;
        $b = ($rgba & 0xFF) / 255;

        if($a > 100) {
          continue;
        }

        $cr = 0;
        if($r < 0.25) {
          $cr = 0;
        } elseif($r < 0.5) {
          $cr = 1;
        } elseif($r < 0.75) {
          $cr = 2;
        } else {
          $cr = 3;
        }

        $cg = 0;
        if($g < 0.25) {
          $cg = 0;
        } elseif($g < 0.5) {
          $cg = 1;
        } elseif($g < 0.75) {
          $cg = 2;
        } else {
          $cg = 3;
        }

        $cb = 0;
        if($b < 0.25) {
          $cb = 0;
        } elseif($b < 0.5) {
          $cb = 1;
        } elseif($b < 0.75) {
          $cb = 2;
        } else {
          $cb = 3;
        }

        $category = ($cb << 4) | ($cg << 2) | ($cr << 0);
        $palette[$category]++;
      }
    }

    $colors = array();

    $max = 0;
    for($i = 0; $i < 64; $i++) {
      if($palette[$max] < $palette[$i]) {
        $max = $i;
      }
    }
    $palette[$max] = -1;
    $colors[] = $max;

    $max = 0;
    for($i = 0; $i < 64; $i++) {
      if($palette[$max] < $palette[$i]) {
        $max = $i;
      }
    }
    $palette[$max] = -1;
    $colors[] = $max;

    $max = 0;
    for($i = 0; $i < 64; $i++) {
      if($palette[$max] < $palette[$i]) {
        $max = $i;
      }
    }
    $palette[$max] = -1;
    $colors[] = $max;

    return $colors;
  }

  // $params[0]: $imagePath
  public static function createPerceptualHash($params) {
    $imagePath = $params[0];

    // Create a smaller image
    $image = self::create8x8($imagePath);

    $greyArray = array();
    $average = 0;
    for($x = 0; $x < 8; $x++) {
      for($y = 0; $y < 8; $y++) {
        $rgb = imagecolorat($image, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        $grey = ($r + $g + $b) / 3;
        $average += $grey;
        $greyArray[] = $grey;
      }
    }
    $average = $average / 64;

    imagedestroy($image);

    $hash = 0;
    foreach($greyArray as $grey) {
      if($grey > $average) {
        $hash++;
      }
      $hash = $hash << 1;
    }

    return $hash;
  }
}

?>
