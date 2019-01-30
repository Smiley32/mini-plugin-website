<?php

class Export_video_plugin {
	// $params[0]: $videoPath
	public static function getFileSize($params) {
		$videoPath = $params[0];

		$ffprobe = Settings::getSetting('ffprobe');
		if($ffprobe == false) {
			return false;
		}

		$cmd = "$ffprobe -v error -show_entries format=size -of default=noprint_wrappers=1 $videoPath";
		$output = shell_exec($cmd);

		return (int)$output;
	}

	// $params[0]: $videoPath
  public static function getSize($params) {
		$videoPath = $params[0];

		$ffprobe = Settings::getSetting('ffprobe');
		if($ffprobe == false) {
			return false;
		}

		$cmd = "$ffprobe -v error -select_streams v:0 -show_entries stream=height,width -of csv=s=x:p=0 $videoPath";
		$output = shell_exec($cmd);

		list($width, $height) = explode('x', $output);

		$size = array();
		$size['width'] = $width;
		$size['height'] = $height;

		return $size;
	}

	// $params[0]: $videoPath
  // $params[1]: $thumbnailPath
  public static function createThumbnail($params) {
    $videoPath = $params[0];
    $thumbnailPath = $params[1];

		$ffmpeg = Settings::getSetting('ffmpeg');
		$ffprobe = Settings::getSetting('ffprobe');

		if($ffmpeg == false || $ffprobe == false) {
			var_dump($ffmpeg);
			return false;
		}

		$cmd = "$ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 $videoPath";
		$output = shell_exec($cmd);

		$max = floatval($output);
		$randomSec = rand(0, (int)$max);

		$cmd = "$ffmpeg -ss $randomSec -i $videoPath -vframes 1 $thumbnailPath.jpg";
		$output = shell_exec($cmd);

		Plugins::callFunction('image_plugin', 'createThumbnail', $thumbnailPath . '.jpg', $thumbnailPath);
		unlink($thumbnailPath . '.jpg');

		return true;
	}
}

?>
