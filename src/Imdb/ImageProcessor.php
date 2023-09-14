<?php

#############################################################################
# IMDBPHP                                                                  #
# JCV personal and dirty class
#############################################################################

namespace Imdb;

class ImageProcessor {

	/**
	 * $width and $height are passed in MdbBase construct 
	 * 800 for both properties by default in Config
	 */
	public function __construct(
		private string $width,
		private string $height
	) {}

	/**
	 * Process with image_resize() ?
	 */
	public function maybe_resize_big($src, $crop=0): bool {

		 if ( str_contains( $src, '_big' ) ) {
			$pic_type = strtolower(strrchr($src,"."));     
			$path_tmp = str_replace( '_big', '_big_tmp', $src );
			$this->image_resize($src, $path_tmp, $this->width, $this->width, 0);
			unlink($src);
			rename( $path_tmp, $src );
			return true;
		}
		return false;
	}

	/**
	 * Resize the pictures, new function, dirtily added here by JCV
	 * https://www.php.net/manual/en/function.imagecopyresampled.php#104028
	 */
	private function image_resize($src, $dst, $width, $height, $crop=0): bool|string {

	  if(!list($w, $h) = getimagesize($src)) return "Unsupported picture type!";

	  $type = strtolower(substr(strrchr($src,"."),1));
	  if($type == 'jpeg') $type = 'jpg';
	  switch($type){
	    case 'bmp': $img = imagecreatefromwbmp($src); break;
	    case 'gif': $img = imagecreatefromgif($src); break;
	    case 'jpg': $img = imagecreatefromjpeg($src); break;
	    case 'png': $img = imagecreatefrompng($src); break;
	    default : return "Unsupported picture type!";
	  }

	  // resize
	  if($crop){
	    if($w < $width or $h < $height) return "Picture is too small!";
	    $ratio = max($width/$w, $height/$h);
	    $h = $height / $ratio;
	    $x = ($w - $width / $ratio) / 2;
	    $w = $width / $ratio;
	  }
	  else{
	    if($w < $width and $h < $height) return "Picture is too small!";
	    $ratio = min($width/$w, $height/$h);
	    $width = $w * $ratio;
	    $height = $h * $ratio;
	    $x = 0;
	  }

	  $new = imagecreatetruecolor( (int) $width, (int) $height);

	  imagecopyresampled($new, $img, 0, 0, (int) $x, 0, (int) $width, (int) $height, (int) $w, (int) $h);

	  switch($type){
	    case 'bmp': imagewbmp($new, $dst); break;
	    case 'gif': imagegif($new, $dst); break;
	    case 'jpg': imagejpeg($new, $dst); break;
	    case 'png': imagepng($new, $dst); break;
	  }
	  return true;
	}

}
